<?php

namespace App\Services;

/**
 * Parses the body a terminal POSTs to /iclock/cdata when it is uploading
 * user / biometric data (table=OPERLOG), rather than attendance punches.
 *
 * Two firmware generations are in the field and both are handled:
 *
 *   Legacy (ZKTeco standalone push):
 *     USER PIN=1\tName=John Doe\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=0000000100000000
 *     FP PIN=1\tFID=0\tSize=1404\tValid=1\tTMP=<base64>
 *     FACE PIN=1\tFID=50\tSIZE=1704\tVALID=1\tTMP=<base64>
 *
 *   Newer (PUSH SDK 2.x "BioData"):
 *     BIODATA Pin=1\tNo=0\tIndex=0\tValid=1\tDuress=0\tType=1\tMajorVer=39
 *             \tMinorVer=39\tFormat=0\tTmp=<base64>
 *     BIOPHOTO PIN=1\tFileName=1.jpg\tType=9\tSize=41356\tContent=<base64 jpeg>
 *
 * Records are separated by CR/LF; fields by TAB. Some firmware uses spaces
 * instead of tabs, so a whitespace fallback is applied — but never a naive
 * split, because Name= legitimately contains spaces and Tmp= is base64 that
 * must survive intact.
 *
 * The parser is deliberately pure: it returns arrays and touches no models,
 * which is what makes it cheap to test against real captured payloads.
 *
 * @author XMindware
 */
class BiometricRecordParser
{
    /** Tags that carry a biometric template. */
    public const TEMPLATE_TAGS = ['FP', 'BIODATA'];

    /** ZKTeco biometric type codes (BIODATA Type=). */
    public const TYPE_FINGERPRINT = 1;

    public const TYPE_FACE_NIR = 2;

    public const TYPE_FACE_VISIBLE = 9;

    /** BIODATA Type= values that are face templates (2 = near-infrared, 9 = visible light). */
    public const TYPES_FACE = [self::TYPE_FACE_NIR, self::TYPE_FACE_VISIBLE];

    /**
     * Parse a full payload into a list of records.
     *
     * Each record is an array with at least a 'kind' key:
     *   - fingerprint : pin, fid, size, valid, duress, template, format, version
     *   - face        : same shape as fingerprint, plus bio_type (2 or 9)
     *   - face_photo  : pin, bio_type, file_name, size, content (base64 JPEG)
     *   - user        : pin, name, card, group, privilege, password
     *   - biometric   : any non-fingerprint biometric (face, palm, vein)
     *   - other       : anything else, kept with its raw line for logging
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(?string $payload): array
    {
        if ($payload === null || trim($payload) === '') {
            return [];
        }

        $records = [];

        foreach (preg_split('/\r\n|\r|\n/', $payload) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $record = $this->parseLine($line);

            if ($record !== null) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Only the fingerprint templates out of a payload.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseFingerprints(?string $payload): array
    {
        return array_values(array_filter(
            $this->parse($payload),
            fn (array $record) => $record['kind'] === 'fingerprint'
        ));
    }

    /**
     * Only the face templates out of a payload.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseFaces(?string $payload): array
    {
        return array_values(array_filter(
            $this->parse($payload),
            fn (array $record) => $record['kind'] === 'face'
        ));
    }

    /**
     * Only the face comparison photos out of a payload.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseFacePhotos(?string $payload): array
    {
        return array_values(array_filter(
            $this->parse($payload),
            fn (array $record) => $record['kind'] === 'face_photo'
        ));
    }

    /**
     * True when the payload looks like user/biometric data rather than
     * attendance punches. Used to decide which ingestion path a cdata POST
     * takes when the terminal does not set table=OPERLOG reliably.
     */
    public function looksLikeBiometricPayload(?string $payload): bool
    {
        if ($payload === null || trim($payload) === '') {
            return false;
        }

        return (bool) preg_match('/^\s*(FP|BIODATA|USER|FACE|BIOPHOTO|USERPIC)\s+/mi', $payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function parseLine(string $line): ?array
    {
        // Tag is the first whitespace-delimited token: "FP", "USER", "BIODATA"...
        if (!preg_match('/^([A-Za-z_]+)\s+(.*)$/s', $line, $m)) {
            return null;
        }

        $tag = strtoupper($m[1]);
        $fields = $this->parseFields($m[2]);

        if ($fields === []) {
            return null;
        }

        return match ($tag) {
            'FP' => $this->fingerprintRecord($fields, $line, 'FP'),
            'BIODATA' => $this->bioDataRecord($fields, $line),
            'USER' => $this->userRecord($fields, $line),
            'FACE' => $this->faceRecord($fields, $line, 'FACE'),
            'BIOPHOTO' => $this->facePhotoRecord($fields),
            default => [
                'kind' => 'other',
                'tag' => $tag,
                'fields' => $fields,
                'raw' => $line,
            ],
        };
    }

    /**
     * Split "K=v\tK2=v2" into an associative array.
     *
     * Tabs are the documented separator. When a firmware sends spaces instead,
     * the fallback regex splits only at " Word=" boundaries so values that
     * contain spaces (Name=) stay whole.
     *
     * @return array<string, string>
     */
    protected function parseFields(string $body): array
    {
        $fields = [];

        $parts = str_contains($body, "\t")
            ? explode("\t", $body)
            : $this->splitOnKeyBoundaries($body);

        foreach ($parts as $part) {
            $part = trim($part, " \r\n");

            if ($part === '' || !str_contains($part, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $part, 2);
            $key = strtolower(trim($key));

            if ($key === '') {
                continue;
            }

            $fields[$key] = $value;
        }

        return $fields;
    }

    /**
     * @return array<int, string>
     */
    protected function splitOnKeyBoundaries(string $body): array
    {
        $parts = preg_split('/\s+(?=[A-Za-z_]+=)/', $body);

        return $parts === false ? [] : $parts;
    }

    /**
     * Legacy "FP PIN=..\tFID=..\tSize=..\tValid=..\tTMP=.." record.
     *
     * @param array<string, string> $fields
     * @return array<string, mixed>|null
     */
    protected function fingerprintRecord(array $fields, string $raw, string $tag): ?array
    {
        $pin = $this->firstOf($fields, ['pin']);
        $template = $this->firstOf($fields, ['tmp', 'template']);

        if ($pin === null || $template === null || $template === '') {
            return null;
        }

        $size = $this->firstOf($fields, ['size']);

        return [
            'kind' => 'fingerprint',
            'tag' => $tag,
            'pin' => (string) $pin,
            'fid' => (int) ($this->firstOf($fields, ['fid', 'no', 'index']) ?? 0),
            'size' => $size !== null && $size !== '' ? (int) $size : strlen($template),
            'valid' => (int) ($this->firstOf($fields, ['valid']) ?? 1),
            'duress' => (int) ($this->firstOf($fields, ['duress']) ?? 0),
            'template' => $template,
            'format' => $this->firstOf($fields, ['format']),
            'version' => $this->version($fields),
            'raw' => $raw,
        ];
    }

    /**
     * Newer "BIODATA Pin=..\tNo=..\tType=1\t..\tTmp=.." record. Type 1 is a
     * fingerprint, 2 and 9 are faces; anything else (palm, vein) is kept but not
     * treated as a template.
     *
     * @param array<string, string> $fields
     * @return array<string, mixed>|null
     */
    protected function bioDataRecord(array $fields, string $raw): ?array
    {
        $type = $this->firstOf($fields, ['type']);

        if ($type !== null && in_array((int) $type, self::TYPES_FACE, true)) {
            return $this->faceRecord($fields, $raw, 'BIODATA');
        }

        if ($type !== null && (int) $type !== self::TYPE_FINGERPRINT) {
            return $this->otherBiometricRecord($fields, $raw, 'biodata-type-' . (int) $type);
        }

        return $this->fingerprintRecord($fields, $raw, 'BIODATA');
    }

    /**
     * Face template: same wire shape as a fingerprint, different kind.
     *
     * @param array<string, string> $fields
     * @return array<string, mixed>|null
     */
    protected function faceRecord(array $fields, string $raw, string $tag): ?array
    {
        $record = $this->fingerprintRecord($fields, $raw, $tag);

        if ($record === null) {
            return null;
        }

        $record['kind'] = 'face';

        // A legacy FACE line carries no type; that record only ever came from
        // near-infrared terminals.
        $type = $this->firstOf($fields, ['type']);
        $record['bio_type'] = $type !== null && $type !== ''
            ? (int) $type
            : self::TYPE_FACE_NIR;

        // In BIODATA a face can be several templates that share No and differ
        // by Index. Keying on No alone would make each one overwrite the last,
        // so fold both into the slot number.
        if (!array_key_exists('fid', $fields) && array_key_exists('index', $fields)) {
            $record['fid'] = ((int) ($fields['no'] ?? 0)) * 100 + (int) $fields['index'];
        }

        return $record;
    }

    /**
     * "BIOPHOTO PIN=..\tFileName=..\tType=9\tSize=..\tContent=<base64 jpeg>" —
     * the comparison photo a visible-light terminal enrols a face from.
     *
     * The raw line is deliberately not kept: it is the photo all over again.
     *
     * @param array<string, string> $fields
     * @return array<string, mixed>|null
     */
    protected function facePhotoRecord(array $fields): ?array
    {
        $pin = $this->firstOf($fields, ['pin']);
        $content = $this->firstOf($fields, ['content']);

        if ($pin === null || $pin === '' || $content === null || $content === '') {
            return null;
        }

        $type = $this->firstOf($fields, ['type']);
        $size = $this->firstOf($fields, ['size']);

        return [
            'kind' => 'face_photo',
            'tag' => 'BIOPHOTO',
            'pin' => (string) $pin,
            'bio_type' => $type !== null && $type !== '' ? (int) $type : self::TYPE_FACE_VISIBLE,
            'file_name' => $this->firstOf($fields, ['filename']),
            'size' => $size !== null && $size !== '' ? (int) $size : strlen($content),
            'content' => $content,
        ];
    }

    /**
     * @param array<string, string> $fields
     * @return array<string, mixed>
     */
    protected function userRecord(array $fields, string $raw): array
    {
        return [
            'kind' => 'user',
            'tag' => 'USER',
            'pin' => (string) ($this->firstOf($fields, ['pin']) ?? ''),
            'name' => $this->firstOf($fields, ['name']),
            'privilege' => $this->firstOf($fields, ['pri', 'privilege']),
            'password' => $this->firstOf($fields, ['passwd', 'password']),
            'card' => $this->firstOf($fields, ['card']),
            'group' => $this->firstOf($fields, ['grp', 'group']),
            'raw' => $raw,
        ];
    }

    /**
     * @param array<string, string> $fields
     * @return array<string, mixed>
     */
    protected function otherBiometricRecord(array $fields, string $raw, string $biotype): array
    {
        return [
            'kind' => 'biometric',
            'biotype' => $biotype,
            'pin' => (string) ($this->firstOf($fields, ['pin']) ?? ''),
            'fid' => (int) ($this->firstOf($fields, ['fid', 'no', 'index']) ?? 0),
            'raw' => $raw,
        ];
    }

    /**
     * @param array<string, string> $fields
     * @param array<int, string> $keys
     */
    protected function firstOf(array $fields, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $fields)) {
                return $fields[$key];
            }
        }

        return null;
    }

    /**
     * @param array<string, string> $fields
     */
    protected function version(array $fields): ?string
    {
        $major = $this->firstOf($fields, ['majorver']);
        $minor = $this->firstOf($fields, ['minorver']);

        if ($major === null && $minor === null) {
            return null;
        }

        return trim(($major ?? '') . '.' . ($minor ?? ''), '.');
    }

    /**
     * Parse the acknowledgement a terminal POSTs to /iclock/devicecmd after
     * running a queued command:
     *
     *   ID=123&Return=0&CMD=DATA
     *
     * Multiple acks may arrive in one body, separated by CR/LF.
     *
     * @return array<int, array{id: int, return: int, cmd: string|null}>
     */
    public static function parseCommandAcks(?string $body): array
    {
        if ($body === null || trim($body) === '') {
            return [];
        }

        $acks = [];

        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            parse_str(str_replace("\t", '&', $line), $parsed);

            $parsed = array_change_key_case($parsed, CASE_LOWER);

            if (!isset($parsed['id']) || !is_numeric($parsed['id'])) {
                continue;
            }

            $acks[] = [
                'id' => (int) $parsed['id'],
                'return' => isset($parsed['return']) && is_numeric($parsed['return'])
                    ? (int) $parsed['return']
                    : -1,
                'cmd' => isset($parsed['cmd']) && is_string($parsed['cmd']) ? $parsed['cmd'] : null,
            ];
        }

        return $acks;
    }
}
