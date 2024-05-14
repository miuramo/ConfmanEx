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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('origname');
            $table->string('mime');
            $table->string('key');
            $table->integer('user_id');
            $table->integer('paper_id')->nullable();
            $table->integer('pagenum')->nullable()->comment('ページ数');
            $table->boolean('valid')->default(true)->comment('有効ならtrue');
            $table->boolean('hidden')->default(false)->comment('不可視ならtrue');
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
