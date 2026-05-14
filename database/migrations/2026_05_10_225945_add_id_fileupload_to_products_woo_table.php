<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products_woo', function (Blueprint $table) {
            $table->unsignedBigInteger('id_fileupload')->nullable()->after('status');
            $table->foreign('id_fileupload')->references('id')->on('file_uploads')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products_woo', function (Blueprint $table) {
            $table->dropForeign(['id_fileupload']);
            $table->dropColumn('id_fileupload');
        });
    }
};
