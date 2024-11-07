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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('navi')->nullable()->comment('ナビゲーション表示')->after('desc');
            $table->integer('orderint')->default(10)->comment('表示順')->after('navi');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('navi');
            $table->dropColumn('orderint');
            //
        });
    }
};
