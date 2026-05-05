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
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
        });

        Schema::table('role_accesses', function (Blueprint $table) {
            $table->string('role_code')->unique()->after('id_role_access')->nullable();
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropForeign(['id_role_access']);
            $table->dropColumn('id_role_access');
            $table->string('role_code')->after('id_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('id_role_access')->after('id_user')->nullable();
            $table->foreign('id_role_access')->references('id_role_access')->on('role_accesses')->onDelete('cascade');
            $table->dropColumn('role_code');
        });

        Schema::table('role_accesses', function (Blueprint $table) {
            $table->dropColumn('role_code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
