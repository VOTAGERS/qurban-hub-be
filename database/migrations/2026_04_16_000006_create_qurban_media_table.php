<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qurban_media', function (Blueprint $table) {
            $table->id('id_media');
            $table->unsignedBigInteger('id_execution');
            $table->string('file_url');
            $table->enum('type', ['photo', 'video'])->default('photo');

            $table->string('status', 10)->default('A');
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_execution')->references('id_execution')->on('qurban_executions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qurban_media');
    }
};
