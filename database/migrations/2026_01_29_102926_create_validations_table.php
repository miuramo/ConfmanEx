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
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id')->comment('イベントID');
            $table->integer('orderint')->comment('表示順');
            $table->string('name')->nullable()->comment('バリデーション名');
            $table->mediumText('script')->nullable()->comment('バリデーションスクリプト');
            $table->mediumText('closure')->nullable()->comment('クロージャ定義');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
