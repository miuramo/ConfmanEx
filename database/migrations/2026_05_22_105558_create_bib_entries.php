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
        Schema::create('bib_entries', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name_jp')->comment('表示名');
            $table->string('name_en')->comment('表示名(英語)');
            $table->string('dtype')->comment('データの種類 varchar, mediumtext');
            $table->boolean('is_required')->default(true)->comment('必須項目かどうか');
            $table->boolean('for_manage')->default(false)->comment('管理用（著者は入力しない）');
            $table->integer('display_order')->default(0)->comment('表示順');
            $table->string('color')->comment('ボタン・ラベルの色');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bib_entries');
    }
};
