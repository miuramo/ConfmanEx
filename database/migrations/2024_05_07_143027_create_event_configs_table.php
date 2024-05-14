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
        Schema::create('event_configs', function (Blueprint $table) {
            $table->comment('EventとEnqueteの紐付け');
            $table->id();
            $table->integer('event_id')->nullable();
            $table->integer('enquete_id')->nullable();
            $table->integer('orderint')->default(0);
            $table->string('openstart', 10)->default('01-01');
            $table->string('openend', 10)->default('12-31');
            $table->boolean('valid')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_configs');
    }
};
