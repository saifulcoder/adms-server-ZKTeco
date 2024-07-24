<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_sn')->unique();
            $table->string('lokasi');
            $table->datetime('online')->nullable();
            // Opsi 1: Menggunakan CURRENT_TIMESTAMP saat insert
            $table->timestamp('created_at')->useCurrent();

            // Opsi 2: Menggunakan CURRENT_TIMESTAMP saat insert dan update
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
