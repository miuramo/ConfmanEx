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
        Schema::table('log_accesses', function (Blueprint $table) {
            $table->index(['uid', 'created_at'], 'idx_log_accesses_uid_created_at');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_accesses', function (Blueprint $table) {
            $table->dropIndex('idx_log_accesses_uid_created_at');
            //
        });
    }
};
