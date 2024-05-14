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
        Schema::create('submits', function (Blueprint $table) {
            $table->comment('査読結果のまとめスコアと採否、ブース番号など');
            $table->id();
            $table->integer('paper_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->double('score')->nullable()->comment('トータルスコア');
            $table->double('stddevscore')->nullable()->comment('スコアの標準偏差');
            $table->integer('accept_id')->default(20)->comment('Acceptの---が初期値');
            $table->boolean('canceled')->default(0)->comment('キャンセルされたら1');
            $table->string('booth')->nullable();
            $table->integer('orderint')->default(0);
            $table->integer('psession_id')->nullable();
            // $table->string('bcolor', 10)->nullable();
            // $table->string('note')->nullable();
            $table->integer('award')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submits');
    }
};
