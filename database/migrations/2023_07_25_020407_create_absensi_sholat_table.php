<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensiSholatTable extends Migration
{
    public function up()
    {
        Schema::create('absensi_sholat', function (Blueprint $table) {
            $table->id();
            $table->string('nis_santri');
            $table->dateTime('table');
            $table->dateTime('waktu');
            $table->text('data');
            $table->date('tgl');
            $table->unsignedBigInteger('id_jadwal_sholat');
            $table->unsignedBigInteger('id_devices');
            $table->timestamps();

            // $table->foreign('id_jadwal_sholat')->references('id')->on('jadwal_sholat')->onDelete('cascade');
            // $table->foreign('id_devices')->references('id')->on('devices')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi_sholat');
    }
}

