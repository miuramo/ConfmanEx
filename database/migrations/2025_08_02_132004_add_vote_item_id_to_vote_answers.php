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
        Schema::table('vote_answers', function (Blueprint $table) {
            $table->integer('vote_item_id')->nullable()->after('vote_id');
            $table->index(['valid', 'vote_id', 'booth']); // インデックスを追加
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vote_answers', function (Blueprint $table) {
            $table->dropIndex(['valid', 'vote_id', 'booth']); // インデックスを削除
            $table->dropColumn('vote_item_id');
            //
        });
    }
};
