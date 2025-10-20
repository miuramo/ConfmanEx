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
            $table->boolean('can_upload_not_accepted')->default(false)->after('pdf_accept_revise')->comment('カメラレディ投稿期間のあいだ、採択者以外がアップロードできるならtrue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('can_upload_not_accepted');
            //
        });
    }
};
