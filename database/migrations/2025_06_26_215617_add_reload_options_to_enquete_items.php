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
            $table->boolean('reload_on_change')->default(false)->after('is_mandatory')->comment("変更時に毎回リロードする？");
            $table->boolean('reload_on_firstinput')->default(false)->after('reload_on_change')->comment("初回入力時にリロードする？");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquete_items', function (Blueprint $table) {
            $table->dropColumn('reload_on_change');
            $table->dropColumn('reload_on_firstinput');
            //
        });
    }
};
