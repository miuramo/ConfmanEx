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
            $table->boolean('closed')->default(false)->after('is_mandatory')->comment('部分的に回答を締め切る場合はtrue');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquete_items', function (Blueprint $table) {
            $table->dropColumn('closed');
        });
    }
};
