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
        Schema::create('affils', function (Blueprint $table) {
            $table->id();
            $table->string('before')->nullable();
            $table->string('matchrule')->nullable()->comment('matching rule');
            $table->string('after')->nullable();
            $table->integer('orderint')->default(0);
            $table->json('pids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affils');
    }
};
