<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** 
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduled_updates', function (Blueprint $table) {
            $table->id(); // target_type / target_id 
            $table->morphs('target');
            $table->string('field_name');
            $table->json('new_value')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('execute_at')->index();
            $table->timestamp('executed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['target_type', 'target_id', 'status']);
        });
    }

    /** 
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_updates');
    }
};

