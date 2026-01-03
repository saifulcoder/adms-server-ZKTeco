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
        // Update the type of the status column in the attendance table
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedInteger('status1')->nullable()->change();
            $table->unsignedInteger('status2')->nullable()->change();
            $table->unsignedInteger('status3')->nullable()->change();
            $table->unsignedInteger('status4')->nullable()->change();
            $table->unsignedInteger('status5')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the type of the status column in the attendance table
        Schema::table('attendances', function (Blueprint $table) {
            $table->tinyInteger('status1')->nullable()->change();
            $table->tinyInteger('status2')->nullable()->change();
            $table->tinyInteger('status3')->nullable()->change();
            $table->tinyInteger('status4')->nullable()->change();
            $table->tinyInteger('status5')->nullable()->change();
        }); 
    }
};
