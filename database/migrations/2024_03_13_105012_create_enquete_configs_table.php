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
        Schema::create('enquete_configs', function (Blueprint $table) {
            $table->comment('アンケートの見せ方');
            $table->id();
            $table->integer('enquete_id')->default(0);
            $table->integer('orderint')->default(123);
            $table->string('catcsv')->nullable()->comment('ex: d1,2,3,d5,d6 dはデモ希望のとき');
            $table->string('openstart', 10)->default('01-01');
            $table->string('openend', 10)->default('12-31');
            $table->boolean('valid')->default(true);
            $table->string('memo')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquete_configs');
    }
};
