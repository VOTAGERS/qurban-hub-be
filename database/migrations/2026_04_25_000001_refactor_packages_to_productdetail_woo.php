<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop foreign key and column id_package from orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['id_package']);
            $table->dropColumn('id_package');
        });

        // 2. Drop packages table
        Schema::dropIfExists('packages');

        // 3. Create productsdetail_woo table
        Schema::create('productsdetail_woo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idproduct_woo');
            $table->string('country', 100);
            $table->integer('max_share')->default(1);

            $table->string('status', 10)->default('A');
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->foreign('idproduct_woo')->references('id')->on('products_woo')->onDelete('cascade');
        });

        // 4. Add idproduct_woo to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('idproduct_woo')->after('id_user')->nullable();
            $table->foreign('idproduct_woo')->references('id')->on('products_woo');
        });
    }

    public function down(): void
    {
        // Rollback is tricky because we dropped tables. In dev we just migrate:fresh.
        Schema::dropIfExists('productsdetail_woo');
    }
};
