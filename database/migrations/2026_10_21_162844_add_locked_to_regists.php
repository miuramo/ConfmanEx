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
        Schema::table('regists', function (Blueprint $table) {
            $table->boolean('locked')->nullable()->comment('編集ロックするならtrue')->after('isearly')->default(false);
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regists', function (Blueprint $table) {
            $table->dropColumn('locked');
        });
    }
};
