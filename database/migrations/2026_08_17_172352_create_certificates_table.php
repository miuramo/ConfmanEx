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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->integer('orderint')->comment("表示順序");
            $table->string('winner')->comment("受賞者の発表番号をカンマ区切りまたは半角スペース区切りで格納");

            $table->string('awardname')->comment("例：優秀論文賞/優秀発表賞/デモ・ポスター賞/学生奨励賞");
            $table->string('year')->comment("例：2026");
            $table->string('eventname')->comment("例：情報教育シンポジウム[:year:]");
            $table->string('creator')->comment("例：@IPSJ");
            $table->string('company')->comment("例：情報処理学会");
            // $table->string('title')->comment("[:eventname:] [:awardname:] 表彰状");
            $table->string('date')->comment("例：令和８年８月２１日");
            // $table->string('description');
            $table->text('content')->comment("貴殿が[:company:][:eventname:]にて発表された[:title:]は特に優秀な論文であり、情報教育の発展に貢献することを認めここに表彰いたします。");
            $table->text('presenter')->comment("[:date:]\r\n一般社団法人 [:company:]\r\n[:eventname:]\r\nプログラム委員長XX XX\r\n実行委員長YY YY\r\n大会委員長ZZ ZZ");
            $table->string('template')->comment("テンプレートファイル名");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
