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
        Schema::create('settings', function (Blueprint $table) {
            $table->comment('環境設定onDB for Confman');
            $table->id();
            $table->string('name')->nullable();
            $table->string('value')->nullable();
            $table->string('misc')->nullable();
            $table->integer('intorder')->default(0);
            $table->boolean('valid')->default(true)->comment('有効');
            $table->boolean('isnumber')->default(false)->comment('valueが数値ならtrue');
            $table->boolean('isbool')->default(false)->comment('valueが真偽値ならtrue、それ以外は文字列');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
