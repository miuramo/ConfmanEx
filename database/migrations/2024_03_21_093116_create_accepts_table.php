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
        Schema::create('accepts', function (Blueprint $table) {
            $table->comment('採択・不採択などの、論文の状態');
            $table->id();
            $table->string('name')->nullable();
            $table->integer('judge')->default(0)->comment("プラスなら採択、マイナスなら不採択、0ならPending");
            // $table->integer('orderint')->default(0);
            $table->string('color', 10)->nullable();
            $table->string('bgcolor', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accepts');
    }
};
