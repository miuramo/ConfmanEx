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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('bgcolor', 10)->default('teal')->comment('背景色');
            $table->string('color', 10)->default('black');

            $table->integer('pdf_page_min')->default(4)->comment("PDFページ数下限");
            $table->integer('pdf_page_max')->default(8)->comment("PDFページ数上限");
            $table->string('pdf_accept_start', 10)->default('01-01')->comment("PDF受入開始日");
            $table->string('pdf_accept_end', 10)->default('12-31')->comment("PDF受入最終日");
            $table->boolean('pdf_accept_revise')->default(0)->comment("PDF差替リクエストを受入れ、Pendingにする");
            $table->integer('accept_video')->default(2)->comment("0は不可 1は必須 2はオプション");
            $table->integer('accept_img')->default(2)->comment("0は不可 1は必須 2はオプション");
            // $table->integer('img_max_size')->default(200);
            $table->integer('img_max_width')->default(480);
            $table->integer('img_max_height')->default(360);
            // $table->boolean('resize_img')->default(0)->comment("画像をmax_width/heightにリサイズ");
            $table->integer('accept_altpdf')->default(2)->comment("0は不可 1は必須 2はオプション");
            $table->integer('altpdf_page_min')->default(1);
            $table->integer('altpdf_page_max')->default(1);

            $table->string('openstart', 10)->default('03-01')->comment("新規投稿受入開始日");
            $table->string('openend', 10)->default('03-31')->comment("新規投稿受入最終日");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
