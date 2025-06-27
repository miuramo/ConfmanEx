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
        Schema::table('enquetes', function (Blueprint $table) {
            // 2025-06-27: Enquete モデルに SoftDeletes を追加したので、ここでカラムを追加する
            $table->softDeletes();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquetes', function (Blueprint $table) {
            // 2025-06-27: Enquete モデルに SoftDeletes を追加したので、ここでカラムを削除する
            $table->dropSoftDeletes();
        });
        //
    }
};
