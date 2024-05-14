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
        /**
         * 利害関係(by 著者emails) csv1
         * × ... 利害関係(by 査読者) csv2
         * 〓 ... 困難 csv3
         * ○ ... 可能 csv4
         * ● ... 希望 csv5
         * ● ... 割り当て希望(by 著者) csv6
         */
        Schema::create('rev_conflicts', function (Blueprint $table) {
            $table->comment('査読拒絶申告、または割当の希望');
            $table->id();
            $table->integer('paper_id')->nullable();
            $table->integer('user_id')->nullable()->comment("ReviewerID");
            $table->integer('author_id')->nullable()->comment('申告したのが著者ならID');
            $table->integer('bidding_id')->default(4)->comment('Paper Bidding: 1なら利害関係者 ');
            $table->string('reason')->nullable();
            $table->boolean('valid')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rev_conflicts');
    }
};
