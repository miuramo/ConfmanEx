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
        Schema::table('passkeys', function (Blueprint $table) {
            // WebAuthn 署名カウンター: クローン認証器の検出に使用
            $table->unsignedBigInteger('counter')->default(0)->after('last_used_at');

            // 認証器がサポートするトランスポート (usb, nfc, ble, internal, hybrid 等)
            $table->json('transports')->nullable()->after('counter');

            // パスキーがクラウドバックアップ済みかどうか (WebAuthn Level 3 BS フラグ)
            $table->boolean('backed_up')->default(false)->after('transports');

            // 認証器の種別: single_device = デバイス固有, multi_device = 同期型
            $table->string('device_type', 20)->default('single_device')->after('backed_up');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passkeys', function (Blueprint $table) {
            $table->dropColumn(['counter', 'transports', 'backed_up', 'device_type']);
        });
    }
};
