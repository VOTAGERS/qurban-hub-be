<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_participants', function (Blueprint $table) {
            $table->id('id_participant');
            $table->unsignedBigInteger('id_order');
            $table->string('qurban_name');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('remarks')->nullable();

            $table->string('status', 10)->default('A');
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_order')->references('id_order')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_participants');
    }
};
