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
        Schema::table('confirms', function (Blueprint $table) {
            $table->boolean('valid')->default(true)->after("grp");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('confirms', function (Blueprint $table) {
            $table->dropColumn('valid');
            //
        });
    }
};
