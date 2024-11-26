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
        Schema::create('annots', function (Blueprint $table) {
            $table->id();
            $table->integer('annot_paper_id')->nullable();
            $table->integer('paper_id')->nullable();
            $table->integer('page')->nullable();
            $table->json('content')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('iine')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annots');
    }
};
