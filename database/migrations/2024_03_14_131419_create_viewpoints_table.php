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
        Schema::create('viewpoints', function (Blueprint $table) {
            $table->comment('査読観点・質問項目');
            $table->id();
            $table->integer('category_id')->default(0);
            $table->integer('orderint')->default(0);
            $table->string('name')->nullable()->comment("key");
            $table->string('desc')->nullable()->comment("keyの説明");
            // $table->string('resultname', 100)->nullable()->comment('査読結果一覧に表示する見出しラベル。表示する場合はここに文字列を設定する。');
            // $table->text('note')->nullable();
            $table->text('content')->nullable()->comment("表示する内容(HTML)");
            $table->text('contentafter')->nullable()->comment("フォーム要素の下に表示する内容(HTML)");
            $table->boolean('forrev')->default(true)->comment("一般査読者が回答する");
            $table->boolean('formeta')->default(false)->comment("メタ査読者が回答する");
            // $table->integer('minScore')->default(1);
            // $table->integer('maxScore')->default(5);
            // $table->tinyInteger('disablezero')->default(0)->comment('1なら0点スコア選べない');
            $table->integer('weight')->default(0)->comment('スコア計算時の重み');
            // $table->boolean('hasScore')->default(false)->comment('1ならスコア追加');
            // $table->boolean('hasReviewComment')->default(false)->comment('1ならコメント追加');
            // $table->boolean('hasHiddenComment')->default(false)->comment('1なら委員会向けコメント追加');
            // $table->tinyInteger('reviewCommentRows')->default(5)->comment('入力フォームの行数');
            // $table->tinyInteger('hiddenCommentRows')->default(5);
            $table->boolean('doReturn')->default(false)->comment('著者に見せるなら1');
            $table->boolean('doReturnAcceptOnly')->default(false)->comment('採択時のみ返す');
            // $table->integer('commentaddscore')->default(0)->comment('著者に返すときにスコアに足す数');
            // $table->text('commentnote')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viewpoints');
    }
};
