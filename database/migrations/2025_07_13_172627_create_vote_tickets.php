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
        Schema::create('vote_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique()->comment('メールアドレス');
            $table->string('token')->unique()->comment('投票トークン');
            $table->string('token_hash')->unique()->comment('投票トークンのハッシュ');
            $table->integer('user_id')->nullable()->comment('User ID'); // associated 
            $table->boolean('valid')->default(true)->comment('有効？');
            $table->boolean('activated')->default(false)->comment('使用開始済み？');
            $table->timestamps();
            $table->datetime('submitted_at')->nullable()->comment('送信日時');
            $table->softDeletes();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_tickets');
        //
    }
};
