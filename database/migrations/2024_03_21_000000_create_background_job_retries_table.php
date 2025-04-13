<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('background_job_retries', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->string('method');
            $table->json('params');
            $table->integer('attempt')->default(1);
            $table->integer('max_attempts');
            $table->integer('delay_seconds');
            $table->timestamp('next_attempt_at');
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('background_job_retries');
    }
}; 