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
        Schema::table('oficinas', function (Blueprint $table) {
            $table->string('city_timezone')->nullable()->after('iatacode');
            $table->string('timezone')->nullable()->after('city_timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oficinas', function (Blueprint $table) {
            $table->dropColumn(['city_timezone', 'timezone']);
        });
    }
};
