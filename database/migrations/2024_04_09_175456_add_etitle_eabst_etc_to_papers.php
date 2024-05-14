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
            $table->string('keyword')->nullable()->after("abst");
            $table->string('etitle')->nullable()->after("keyword");
            $table->mediumText('eabst')->nullable()->after("etitle");
            $table->string('ekeyword')->nullable()->after("eabst");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn('keyword');
            $table->dropColumn('etitle');
            $table->dropColumn('eabst');
            $table->dropColumn('ekeyword');
        });
    }
};
