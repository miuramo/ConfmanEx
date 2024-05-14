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
        Schema::create('log_modifies', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->default(0)->comment('操作者');
            $table->string('table')->nullable();
            $table->integer('target_id')->default(0)->comment('修正ターゲットデータのID');
            $table->mediumText('diff')->nullable()->comment('diff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_modifies');
    }
};
