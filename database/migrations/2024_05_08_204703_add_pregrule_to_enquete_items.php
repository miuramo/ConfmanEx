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
        Schema::table('enquete_items', function (Blueprint $table) {
            $table->string('pregrule', 100)->nullable()->comment("正規表現ルール")->after("is_mandatory");
            $table->string('pregerrmes', 100)->nullable()->comment("正規表現ルールに一致しないときのメッセージ")->after("pregrule");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquete_items', function (Blueprint $table) {
            $table->dropColumn('pregrule');
            $table->dropColumn('pregerrmes');
        });
    }
};
