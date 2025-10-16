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
        Schema::table('regists', function (Blueprint $table) {
            $table->boolean('canceled')->default(false)->after('isearly');
            $table->dateTime('canceled_at')->nullable()->after('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regists', function (Blueprint $table) {
            $table->dropColumn('canceled');
            $table->dropColumn('canceled_at');
            //
        });
    }
};
