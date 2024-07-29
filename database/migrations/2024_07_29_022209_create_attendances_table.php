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
     Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->dateTime('timestamp');
            $table->boolean('status1');
            $table->boolean('status2');
            $table->boolean('status3');
            $table->boolean('status4');
            $table->boolean('status5');
            $table->boolean('status6');
            $table->boolean('status7');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
