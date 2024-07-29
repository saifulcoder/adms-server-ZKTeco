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
        Schema::create('finger_log', function (Blueprint $table) {
        $table->id();
        $table->text('data');
        $table->text('url');
        // Opsi 1: Menggunakan CURRENT_TIMESTAMP saat insert
        $table->timestamp('created_at')->useCurrent();

        // Opsi 2: Menggunakan CURRENT_TIMESTAMP saat insert dan update
        $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
