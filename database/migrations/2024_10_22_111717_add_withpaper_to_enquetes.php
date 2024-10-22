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
            $table->boolean('withpaper')->default(1)->comment('Paperに関連するなら1 (参加登録関係は0)')->after('showonreviewerindex');
            
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquetes', function (Blueprint $table) {
            $table->dropColumn('withpaper');
            //
        });
    }
};
