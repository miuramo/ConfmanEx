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
        Schema::create('vote_items', function (Blueprint $table) {
            $table->id();
            $table->integer('vote_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('orderint')->default(1);
            $table->json('submits')->nullable();
            $table->integer('upperlimit')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_items');
    }
};
