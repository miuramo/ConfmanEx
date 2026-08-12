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
        Schema::table('enquetes', function (Blueprint $table) {
            $table->boolean('is_independently_editable')->default(false)->after('withpaper')->comment('参加登録完了後に、個別に編集可能にするかどうか');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquetes', function (Blueprint $table) {
            $table->dropColumn('is_independently_editable');
        });
    }
};
