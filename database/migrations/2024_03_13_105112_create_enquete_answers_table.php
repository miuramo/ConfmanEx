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
        Schema::create('enquete_answers', function (Blueprint $table) {
            $table->comment('アンケートの個々の回答');
            $table->id();
            $table->integer('enquete_id')->default(0);
            $table->integer('enquete_item_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('paper_id')->default(0);
            $table->integer('value')->nullable();
            $table->text('valuestr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquete_answers');
    }
};
