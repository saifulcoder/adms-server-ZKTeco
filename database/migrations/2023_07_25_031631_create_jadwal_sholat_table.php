<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalSholatTable extends Migration
{
    public function up()
    {
        Schema::create('jadwal_sholat', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->time('dari');
            $table->time('sampai');
            $table->boolean('terlambat')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_sholat');
    }
}
