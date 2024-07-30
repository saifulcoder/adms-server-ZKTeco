<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_handshake_configs', function (Blueprint $table) {
            $table->id();
            $table->string('device_type')->default('default');
            $table->integer('stamp')->default(9999);
            $table->integer('error_delay')->default(60);
            $table->integer('delay')->default(30);
            $table->integer('res_log_day')->default(18250);
            $table->integer('res_log_del_count')->default(10000);
            $table->integer('res_log_count')->default(50000);
            $table->string('trans_times')->default('00:00;14:05');
            $table->integer('trans_interval')->default(1);
            $table->string('trans_flag', 10)->default('1111000000');
            $table->integer('time_zone')->default(7);
            $table->boolean('realtime')->default(true);
            $table->boolean('encrypt')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_handshake_configs');
    }
};