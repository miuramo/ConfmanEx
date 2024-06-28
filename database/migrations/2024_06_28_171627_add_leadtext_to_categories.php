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
            $table->mediumText('leadtext')->nullable()->comment("カテゴリ固有の案内(リード文)")->after("status__revreturn_on");
            // "__memo 投稿カテゴリ固有の案内は、ここに設定してください。<b>HTMLタグ</b>もつかえますし、TailwindCSSの記法もだいたいはつかえます。<br>先頭が__ではじまっているか、空にすると、非表示になります。"
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
