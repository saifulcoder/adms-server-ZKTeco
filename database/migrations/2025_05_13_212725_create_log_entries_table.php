<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('log_entries', function (Blueprint $table) {
        $table->id();
        $table->string('sn', 50);              // device serial number
        $table->timestamp('log_time');         // extracted or fallback timestamp
        $table->text('message');               // actual log line
        $table->timestamps();                  // created_at, updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_entries');
    }
};
