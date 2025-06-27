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
        Schema::create('regists', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->comment('User ID');
            $table->boolean('valid')->default(false)->comment('有効？');
            $table->boolean('isearly')->default(false)->comment('早期？');
            $table->integer('fee')->default(0)->comment('参加費');
            $table->boolean('paid')->default(false)->comment('支払い済み？');
            $table->datetime('paid_at')->nullable()->comment('支払い日時');
            $table->string('payment_method')->nullable()->comment('支払い方法');
            $table->string('payment_id')->nullable()->comment('支払いID');
            $table->string('payment_status')->nullable()->comment('支払いステータス');
            $table->datetime('confirmed_at')->nullable()->comment('確認日時');
            $table->string('memo')->nullable()->comment('メモ');
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
        Schema::dropIfExists('regists');
        //
    }
};
