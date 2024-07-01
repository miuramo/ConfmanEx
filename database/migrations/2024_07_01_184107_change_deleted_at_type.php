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
        Schema::table('papers', function (Blueprint $table) {
            // deleted列が存在する場合は一度削除して再度追加する
            $table->dropColumn('deleted');
        });

        Schema::table('papers', function (Blueprint $table) {
            // deleted_at列をtimestamp型で再追加する
            $table->softDeletes();
            // $table->timestamp('deleted_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('papers', function (Blueprint $table) {
            // deleted_at列をtinyint型で再追加する
            $table->tinyInteger('deleted')->nullable()->default(null);
        });
    }
};
