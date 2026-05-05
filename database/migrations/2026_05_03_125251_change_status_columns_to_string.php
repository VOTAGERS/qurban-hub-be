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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 20)->change();
            $table->string('qurban_status', 20)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_status', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Untuk revert, kita bisa kembalikan ke enum jika perlu, 
        // tapi biasanya string ke enum agak berisiko jika data tidak valid.
    }
};
