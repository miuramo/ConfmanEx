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
        Schema::create('mail_spools', function (Blueprint $table) {
            $table->comment('一括送信メールの蓄積');
            $table->id();
            $table->integer('user_id')->nullable()->comment("作成者");
            $table->string('name')->nullable();
            $table->longText('obj')->nullable();
            $table->boolean('pending')->default(false);
            $table->boolean('issent')->default(false);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_spools');
    }
};
