<?php

namespace App\Services;

use App\Models\Agente;
use App\Models\Command;
use App\Models\Device;
use App\Models\FaceTemplate;
use App\Models\FingerprintTemplate;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Takes the raw body a terminal POSTs to /iclock/cdata and turns any biometric
 * templates in it into rows.
 *
 * This is the receiving half of the pull: PullFingerprintsService asks, the
 * terminal answers here, minutes later, over a completely separate request.
 *
 * Storage is idempotent — a template that comes back unchanged (same finger,
 * same bytes) touches `captured_at` and nothing else, so re-pulling a device is
 * safe and cheap.
 *
 * @author XMindware
 */
class FingerprintIngestService
{
    public function __construct(
        protected BiometricRecordParser $parser
    ) {
    }

    /**
     * @return array{fingerprints: int, faces: int, stored: int, updated: int, unchanged: int, users: int, other: int}
     */
    public function ingest(?string $sn, ?string $payload, ?Device $device = null): array
    {
        $result = [
            'fingerprints' => 0,
            'faces' => 0,
            'stored' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'users' => 0,
            'other' => 0,
        ];

        $records = $this->parser->parse($payload);

        if ($records === []) {
            return $result;
        }

        $device ??= Device::where('serial_number', $sn)->first();
        $sn = $sn ?: ($device->serial_number ?? null);

        if ($sn === null) {
            Log::warning('FingerprintIngestService: payload without a serial number, ignored');

            return $result;
        }

        $touchedPins = [];

        foreach ($records as $record) {
            if ($record['kind'] === 'user') {
                $result['users']++;
                continue;
            }

            if ($record['kind'] === 'face') {
                $result['faces']++;

                try {
                    $this->storeFaceTemplate($sn, $device, $record);
                } catch (Throwable $e) {
                    Log::error('FingerprintIngestService: failed to store face template', [
                        'sn' => $sn,
                        'pin' => $record['pin'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }

                continue;
            }

            if ($record['kind'] !== 'fingerprint') {
                $result['other']++;
                continue;
            }

            $result['fingerprints']++;

            try {
                $outcome = $this->storeTemplate($sn, $device, $record);
                $result[$outcome]++;
                $touchedPins[$record['pin']] = true;
            } catch (Throwable $e) {
                Log::error('FingerprintIngestService: failed to store template', [
                    'sn' => $sn,
                    'pin' => $record['pin'] ?? null,
                    'fid' => $record['fid'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        foreach (array_keys($touchedPins) as $pin) {
            $this->refreshAgenteFingerprintIndex($device, (string) $pin);
        }

        if ($result['fingerprints'] > 0 || $result['faces'] > 0) {
            Log::info('FingerprintIngestService: templates ingested', array_merge($result, ['sn' => $sn]));
        }

        return $result;
    }

    /**
     * Upsert one face template, keyed (sn, pin, fid) like fingerprints.
     *
     * @param array<string, mixed> $record
     */
    protected function storeFaceTemplate(string $sn, ?Device $device, array $record): void
    {
        FaceTemplate::updateOrCreate(
            ['sn' => $sn, 'pin' => (string) $record['pin'], 'fid' => (int) $record['fid']],
            [
                'device_id' => $device->id ?? null,
                'size' => $record['size'],
                'valid' => $record['valid'],
                'template' => $record['template'],
                'template_hash' => sha1((string) $record['template']),
                'idempresa' => $device->idempresa ?? null,
                'idoficina' => $device->idoficina ?? null,
                'format' => $record['format'],
                'version' => $record['version'],
                'captured_at' => now(),
            ]
        );
    }

    /**
     * @param array<string, mixed> $record
     * @return 'stored'|'updated'|'unchanged'
     */
    protected function storeTemplate(string $sn, ?Device $device, array $record): string
    {
        $hash = sha1((string) $record['template']);

        $existing = FingerprintTemplate::where('sn', $sn)
            ->where('pin', $record['pin'])
            ->where('fid', $record['fid'])
            ->first();

        // Was this finger explicitly asked for? If so the answer closes the
        // command, and the row is attributed to a query rather than an enrolment.
        $source = $this->closeMatchingPullCommand($device, (string) $record['pin'], (int) $record['fid'])
            ? 'query'
            : 'push';

        $attributes = [
            'device_id' => $device->id ?? null,
            'size' => $record['size'],
            'valid' => $record['valid'],
            'duress' => $record['duress'],
            'template' => $record['template'],
            'template_hash' => $hash,
            'idempresa' => $device->idempresa ?? null,
            'idoficina' => $device->idoficina ?? null,
            'source' => $source,
            'format' => $record['format'],
            'version' => $record['version'],
            'captured_at' => now(),
        ];

        if ($existing === null) {
            FingerprintTemplate::create(array_merge($attributes, [
                'sn' => $sn,
                'pin' => (string) $record['pin'],
                'fid' => (int) $record['fid'],
            ]));

            return 'stored';
        }

        $unchanged = $existing->template_hash === $hash;

        if ($unchanged) {
            // Same bytes: only record that we saw it again.
            $existing->forceFill([
                'captured_at' => now(),
                'source' => $source,
            ])->save();

            return 'unchanged';
        }

        $existing->fill($attributes)->save();

        return 'updated';
    }

    /**
     * Mark the DATA QUERY FINGERTMP command for this PIN/FID as completed.
     *
     * Returns true when such a command existed, which is how we know the
     * template arrived because we asked rather than because someone enrolled a
     * finger on the terminal.
     */
    protected function closeMatchingPullCommand(?Device $device, string $pin, int $fid): bool
    {
        if ($device === null) {
            return false;
        }

        $command = $device->commands()
            ->where('type', PullFingerprintsService::TYPE_QUERY_FP)
            ->where('reference', "PIN:{$pin}/FID:{$fid}")
            ->whereNull('completed_at')
            ->orderByDesc('id')
            ->first();

        if (!$command instanceof Command) {
            return false;
        }

        $command->forceFill([
            'completed_at' => now(),
            'executed_at' => $command->executed_at ?? now(),
        ])->save();

        return true;
    }

    /**
     * Keep agentes.fingerprint_data as a small index of which fingers we hold
     * for this employee — sizes and hashes, never the templates themselves,
     * which live in fingerprint_templates and would not fit here.
     */
    protected function refreshAgenteFingerprintIndex(?Device $device, string $pin): void
    {
        if ($device === null) {
            return;
        }

        $agente = Agente::where('idagente', $pin)
            ->where('idempresa', $device->idempresa)
            ->where('idoficina', $device->idoficina)
            ->first();

        if ($agente === null) {
            return;
        }

        $index = FingerprintTemplate::where('pin', $pin)
            ->where('idempresa', $device->idempresa)
            ->where('idoficina', $device->idoficina)
            ->orderBy('fid')
            ->get()
            ->map(fn (FingerprintTemplate $t) => [
                'fid' => $t->fid,
                'sn' => $t->sn,
                'size' => $t->size,
                'valid' => $t->valid,
                'hash' => $t->template_hash,
                'captured_at' => optional($t->captured_at)->toIso8601String(),
            ])
            ->values()
            ->all();

        $agente->forceFill([
            'fingerprint_data' => json_encode($index, JSON_UNESCAPED_SLASHES),
        ])->save();
    }
}
