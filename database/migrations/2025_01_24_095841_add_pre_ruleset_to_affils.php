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
        Schema::table('affils', function (Blueprint $table) {
            $table->string('origtxt')->nullable()->after('pids');
            $table->boolean('pre')->default(false)->after('orderint');
            $table->integer('ruleset')->default(1)->after('pre');
            $table->boolean('skip')->default(false)->after('ruleset');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affils', function (Blueprint $table) {
            $table->dropColumn('pre');
            $table->dropColumn('ruleset');
            $table->dropColumn('skip');
            $table->dropColumn('origtxt');
            //
        });
    }
};
