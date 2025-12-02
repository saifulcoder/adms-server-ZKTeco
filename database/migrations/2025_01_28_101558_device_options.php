<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_options', function (Blueprint $table) {
            $table->id();
            $table->string('sn');
            $table->string('table')->nullable();
            $table->timestamp('stamp')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->timestamp('timestamp')->nullable();
            $table->integer('status1')->nullable();
            $table->integer('status2')->nullable();
            $table->integer('status3')->nullable();
            $table->integer('status4')->nullable();
            $table->integer('status5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_options');
    }
};