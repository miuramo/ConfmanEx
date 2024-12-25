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
        Schema::table('viewpoints', function (Blueprint $table) {
            $table->boolean('mandatory')->default(true)->after('formeta')->comment('回答必須');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewpoints', function (Blueprint $table) {
            $table->dropColumn('mandatory');
            //
        });
    }
};
