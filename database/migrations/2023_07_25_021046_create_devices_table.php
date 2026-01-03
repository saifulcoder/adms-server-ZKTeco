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
            $table->string('name')->nullable();
            $table->string('serial_number')->unique();
            $table->string('idempresa')->nullable();
            $table->string('idoficina')->nullable();
            $table->string('idreloj')->nullable();
            $table->datetime('online')->nullable();
            $table->string('public_ip')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
