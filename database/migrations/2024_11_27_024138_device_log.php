<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('device_log')) {
            Schema::create('device_log', function (Blueprint $table) {
                $table->id();
                $table->json('data');
                $table->timestamp('tgl');
                $table->string('sn');
                $table->string('option');
                $table->string('url');
                $table->timestamps();
            });
        }

        if(!Schema::hasColumn('device_log','idreloj')){
            Schema::table('device_log', function (Blueprint $table) {
                $table->string('idreloj')->default('999999');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_log');
    }
};
