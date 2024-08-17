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
        Schema::create('vote_answers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('vote_id')->nullable();
            $table->integer('submit_id')->nullable();
            $table->string('booth')->nullable();
            $table->boolean('valid')->default(true);
            $table->text('comment')->nullable();
            $table->string('token')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('token');
            $table->index('booth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_answers');
    }
};
