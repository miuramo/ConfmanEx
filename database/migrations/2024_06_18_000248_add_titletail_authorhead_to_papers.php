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
        Schema::table('papers', function (Blueprint $table) {
            $table->string('titletail', 100)->nullable()->comment("タイトルの最後の単語")->after("title");
            $table->string('authorhead', 100)->nullable()->comment("最初の著者の単語")->after("titletail");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn('titletail');
            $table->dropColumn('authorhead');
            //
        });
    }
};
