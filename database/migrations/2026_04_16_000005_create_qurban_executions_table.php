<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qurban_executions', function (Blueprint $table) {
            $table->id('id_execution');
            $table->unsignedBigInteger('id_order');
            $table->date('execution_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('execution_status', ['pending', 'completed'])->default('pending');

            $table->string('status', 10)->default('A');
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_order')->references('id_order')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qurban_executions');
    }
};
