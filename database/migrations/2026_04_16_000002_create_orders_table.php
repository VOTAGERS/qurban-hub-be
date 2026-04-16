<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->string('order_code', 100)->unique();

            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_package');

            $table->integer('quantity');
            $table->decimal('total_price', 12, 2);

            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('qurban_status', ['pending', 'scheduled', 'completed'])->default('pending');

            $table->string('status', 10)->default('A');
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_package')->references('id_package')->on('packages');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
