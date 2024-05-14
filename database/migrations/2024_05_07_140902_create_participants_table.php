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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('event_id')->nullable();
            $table->dateTime('submitted')->nullable()->comment("登録完了");
            $table->boolean('valid')->default(false)->comment("登録完了したらtrue");
            $table->boolean('paid')->default(false);
            $table->boolean('early')->default(true);
            $table->mediumText('memo')->nullable();
            $table->softDeletes(); // canceled = deleted_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
