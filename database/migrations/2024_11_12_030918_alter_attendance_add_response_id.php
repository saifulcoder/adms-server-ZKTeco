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
         // add response_id column to attendances table
         Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('response_uniqueid')->nullable()->after('status5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // if field response_uniqueid exists, drop it
        if (Schema::hasColumn('attendances', 'response_uniqueid')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('response_uniqueid');
            });
        }
    }
};
