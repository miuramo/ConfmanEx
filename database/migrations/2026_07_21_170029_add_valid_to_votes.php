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
        Schema::table('votes', function (Blueprint $table) {
            $table->integer('category_id')->nullable()->after('name'); // カテゴリIDを追加。対応しない場合はnullのまま。
            $table->boolean('valid')->default(true)->after('category_id');
            $table->boolean('separate_student')->default(false)->after('valid'); // 学生発表と一般発表を分けるかどうか
            $table->text('student_paper_ids')->nullable()->after('separate_student'); // 学生発表のpaper_idをカンマ区切りで保存
            $table->float('percentage_upperlimit')->default(0.2)->after('separate_student'); // 投票上限数を設定するときのデフォルトの割合。デフォルトは0.2。
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->dropColumn('valid');
            $table->dropColumn('separate_student');
            $table->dropColumn('student_paper_ids');
            $table->dropColumn('percentage_upperlimit');
        });
    }
};
