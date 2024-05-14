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
        Schema::create('enquete_items', function (Blueprint $table) {
            $table->comment('アンケートの質問項目');
            $table->id();
            $table->integer('enquete_id')->default(0);
            $table->integer('orderint')->default(0);
            $table->string('name', 50)->nullable()->comment("key");
            $table->string('desc', 50)->nullable()->comment("keyの説明");
            $table->mediumText('content')->nullable()->comment("表示する内容(HTML)");
            $table->mediumText('contentafter')->nullable()->comment("フォーム要素の下に表示する内容(HTML)");
            $table->boolean('is_mandatory')->default(true)->comment("オプション項目なら、falseにする（通常は必須）");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquete_items');
    }
};
