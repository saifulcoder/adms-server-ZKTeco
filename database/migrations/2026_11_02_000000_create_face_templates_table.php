<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Face templates pulled from the terminals. Kept apart from
 * `fingerprint_templates` so the (sn, pin, fid) uniqueness of fingers is not
 * shared with face slots, and so face payloads (often 10 KB+) do not bloat the
 * fingerprint table.
 *
 * @author XMindware
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_templates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('device_id')->nullable()->index();
            $table->string('sn')->index();

            // PIN as reported by the terminal == agentes.idagente
            $table->string('pin')->index();
            // Face slot (FID / No depending on firmware)
            $table->unsignedSmallInteger('fid')->default(0);

            $table->unsignedInteger('size')->nullable();
            $table->unsignedTinyInteger('valid')->default(1);

            $table->longText('template');
            $table->string('template_hash', 40)->index();

            $table->string('idempresa')->nullable()->index();
            $table->string('idoficina')->nullable()->index();

            $table->string('format', 16)->nullable();
            $table->string('version', 16)->nullable();

            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->unique(['sn', 'pin', 'fid'], 'face_templates_sn_pin_fid_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_templates');
    }
};
