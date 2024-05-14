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
        Schema::create('reviews', function (Blueprint $table) {
            $table->comment('査読割り当て');
            $table->id();
            $table->integer('submit_id')->nullable();
            $table->integer('paper_id')->nullable()->comment("本来はsubmit_idのみでよいが、実装の都合で");
            $table->integer('user_id')->nullable()->comment("ReviewerID");
            $table->integer('category_id')->nullable()->comment("本来はsubmit_idのみでよい、実装の都合で");
            $table->boolean('ismeta')->default(false);
            $table->integer('status')->nullable()->comment("0は未回答、1は回答中、2は完了");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
