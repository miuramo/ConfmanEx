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
        Schema::table('vote_tickets', function (Blueprint $table) {
            // 送信回数を追加
            $table->unsignedInteger('sentnum')->default(0)->after('user_id')->comment('送信した回数');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vote_tickets', function (Blueprint $table) {
            // 送信済みの投票チケット数を削除
            $table->dropColumn('sentnum');
            //
        });
    }
};
