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
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->string('from')->default('[[:MAILFROM:]]')->change();
            $table->text('to')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->string('from')->default('[:MAILFROM:]')->change();
            $table->string('to')->nullable()->change();
        });
    }
};
