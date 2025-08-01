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
        Schema::table('vote_items', function (Blueprint $table) {
            $table->string('desc')->nullable()->after('name'); // Adding 'desc' column after 'name'
            $table->boolean('show_pdf_link')->default(false)->after('desc'); // Adding 'show_pdf_link' column
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vote_items', function (Blueprint $table) {
            $table->dropColumn('desc'); // Dropping 'desc' column
            $table->dropColumn('show_pdf_link'); // Dropping 'show_pdf_link' column
            //
        });
    }
};
