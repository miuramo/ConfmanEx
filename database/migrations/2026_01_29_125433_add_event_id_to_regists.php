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
            $table->integer('event_id')->default(1)->comment('イベントID')->after('id');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regists', function (Blueprint $table) {
            $table->dropColumn('event_id');
        });
    }
};
