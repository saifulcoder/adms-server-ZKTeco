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
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'idoficina')) {    
                $table->unsignedSmallInteger('idoficina')->nullable();
                $table->foreign('idoficina')->references('idoficina')->on('oficinas');
                $table->string('modelo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['idoficina']);
            $table->dropColumn('idoficina');
            $table->dropColumn('modelo');
        });
    }
};
