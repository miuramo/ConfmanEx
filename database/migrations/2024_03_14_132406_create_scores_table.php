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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->comment('査読結果');
            $table->integer('review_id')->nullable();
            $table->integer('viewpoint_id')->nullable();
            $table->integer('user_id')->default(0);
            $table->integer('value')->nullable();
            $table->text('valuestr')->nullable();
            // $table->integer('score')->default(0);
            // $table->integer('weight')->default(0)->comment('重み');
            // $table->mediumText('reviewcomment')->nullable();
            // $table->mediumText('hiddencomment')->nullable();
            // $table->dateTime('created')->nullable();
            // $table->dateTime('modified')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
