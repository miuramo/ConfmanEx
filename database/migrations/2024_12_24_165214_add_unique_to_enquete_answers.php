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
        Schema::table('enquete_answers', function (Blueprint $table) {
            $table->unique(
                ['enquete_id', 'enquete_item_id', 'user_id', 'paper_id'],
                'unique_enquete_answers'
            );
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquete_answers', function (Blueprint $table) {
            $table->dropUnique(['enquete_id', 'enquete_item_id', 'user_id', 'paper_id']);
            //
        });
    }
};
