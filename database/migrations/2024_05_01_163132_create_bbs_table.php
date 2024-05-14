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
        Schema::create('bbs', function (Blueprint $table) {
            $table->comment('掲示板の種類とアクセス制限');
            $table->id();
            $table->string('name')->nullable();
            $table->integer('paper_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('type')->default(1)->comment('1なら事前議論 2ならメタと著者 3なら出版と著者');
            $table->string('key')->nullable();
            $table->boolean('needreply')->default(false);
            $table->boolean('isopen')->default(true);
            $table->boolean('isclose')->default(false);
            $table->string('subscribers')->nullable()->comment('author|pc|metareviewer|reviewer|pub|admin');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbs');
    }
};
