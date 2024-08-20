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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('email',191)->unique();
            $table->integer('errorcount')->default(0)->comment('送信エラー回数');
            $table->integer('infoprovider')->nullable()->comment('情報提供者');
            $table->string('memo')->nullable()->comment('管理メモ');
            $table->boolean('valid')->default(true)->comment('有効ならtrue');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
