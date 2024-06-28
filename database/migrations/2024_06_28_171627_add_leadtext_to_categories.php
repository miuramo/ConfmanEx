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
        Schema::table('categories', function (Blueprint $table) {
            $table->mediumText('leadtext')->nullable()->default("__memo 投稿カテゴリ固有の案内は、ここに設定してください。<b>HTMLタグ</b>もつかえますし、<span class='underline bg-yellow-200'>TailwindCSSの記法</span>もだいたいはつかえます。<br>先頭が__ではじまっているか、空にすると、非表示になります。")->comment("カテゴリ固有の案内(リード文)")->after("status__revreturn_on");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('leadtext');
            //
        });
    }
};
