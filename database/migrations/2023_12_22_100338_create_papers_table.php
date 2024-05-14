<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('papers', function (Blueprint $table) {
            $table->comment('論文');
            $table->id();
            $table->integer('category_id')->default(0)->comment('投稿時のカテゴリ');
            $table->integer('owner')->default(0)->comment('投稿者uID');;
            $table->string('title')->nullable();
            $table->mediumText('authorlist')->nullable()->comment('著者リスト');
            $table->mediumText('abst')->nullable();
            $table->mediumText('contactemails')->nullable()->comment('連絡メールアドレス');
            // $table->boolean('demoifaccepted')->default(false)->comment('採択時にデモ希望する');
            // $table->boolean('nopublishcatalog')->default(false)->comment('Webカタログの公開を論文公知日まで待って欲しい');
            $table->string('remarks')->nullable();

            $table->integer('pdf_file_id')->nullable()->comment('論文PDFファイルのid');
            $table->integer('img_file_id')->nullable()->comment('Webカタログ画像などキャッチイメージファイルのid');
            $table->integer('video_file_id')->nullable()->comment('動画');
            $table->integer('altpdf_file_id')->nullable()->comment('ティザー資料');

            $table->string('zipcode', 10)->nullable();
            $table->mediumText('address')->nullable()->comment('old');
            $table->string('telnum', 30)->nullable();
            $table->integer('registid')->default(0);
            $table->boolean('locked')->default(false)->comment('編集ロック');
            // $table->mediumText('history')->nullable()->comment('編集操作履歴');
            $table->dateTime('created_at')->nullable()->comment('mod for Laravel');
            $table->dateTime('updated_at')->nullable()->comment('mod for Laravel');
            $table->boolean('accepted')->default(false)->comment('投稿が採択されたらtrue');
            $table->integer('status_id')->default(0)->comment('投稿ステータス');
            $table->boolean('valid')->default(false)->comment('投稿が健全になったらtrue');
            // $table->boolean('hidden')->default(false)->comment('不可視ならtrue');
            $table->boolean('deleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('papers');
    }
};
