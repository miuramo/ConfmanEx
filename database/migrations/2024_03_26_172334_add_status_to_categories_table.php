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
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('status__revreturn_on')->default(false)->comment("査読結果を著者に開示")->after("openend");
            $table->boolean('status__revlist_on')->default(false)->comment("査読結果一覧を開示（PC・査読者）")->after("openend");
            $table->boolean('status__revedit_off')->default(false)->comment("査読編集ロック")->after("openend");
            $table->boolean('status__revedit_on')->default(false)->comment("査読中はtrue")->after("openend");
            $table->boolean('status__bidding_off')->default(false)->comment("Bidding終了")->after("openend");
            $table->boolean('status__bidding_on')->default(false)->comment("Bidding開始")->after("openend");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('status__bidding_on');
            $table->dropColumn('status__bidding_off');
            $table->dropColumn('status__revedit_on');
            $table->dropColumn('status__revedit_off');
            $table->dropColumn('status__revlist_on');
            $table->dropColumn('status__revreturn_on');
        });
    }
};
