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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('altpdf_accept_start', 10)->default('02-14')->after('altpdf_page_max')->comment("AltPDF受入開始日 (投稿Lock後も特別解放する)");
            $table->string('altpdf_accept_end', 10)->default('02-24')->after('altpdf_accept_start')->comment("AltPDF受入最終日 (投稿Lock後も特別解放する)");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('altpdf_accept_start');
            $table->dropColumn('altpdf_accept_end');
            //
        });
    }
};
