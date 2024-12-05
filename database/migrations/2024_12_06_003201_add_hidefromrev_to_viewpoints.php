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
            $table->boolean('hidefromrev')->default(false)->after('formeta')->comment('一般査読者からは隠す');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewpoints', function (Blueprint $table) {
            $table->dropColumn('hidefromrev');
            //
        });
    }
};
