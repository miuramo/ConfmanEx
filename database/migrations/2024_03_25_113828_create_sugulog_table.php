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
        Schema::create('sugulog', function (Blueprint $table) {
            $table->id();
            $table->mediumText('name')->nullable();
            $table->text('type')->nullable();
            $table->longText('backtrace')->nullable();
            $table->tinyInteger('iscli')->default(0);
            $table->integer('uid')->default(0);
            $table->timestamp('created')->useCurrent();
            $table->tinyInteger('deleted')->default(0);
            $table->tinyInteger('istodo')->default(0);
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sugulog');
    }
};
