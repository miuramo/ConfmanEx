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
        Schema::table('files', function (Blueprint $table) {
            $table->boolean('archived')->default(false)->after('pending')->comment('archived');
            $table->boolean('destroy_prohibited')->default(false)->after('archived')->comment('著者による削除禁止');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('archived');
            $table->dropColumn('destroy_prohibited');
            //
        });
    }
};
