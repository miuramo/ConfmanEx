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
            $table->text('subdesc')->nullable()->after('doReturnAcceptOnly')->comment('返す時の補足説明');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewpoints', function (Blueprint $table) {
            $table->dropColumn('subdesc');
            //
        });
    }
};
