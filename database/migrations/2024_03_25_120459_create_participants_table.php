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
        // Schema::create('participants', function (Blueprint $table) {
        //     $table->id();
        //     $table->integer('user_id')->default(0);
        //     $table->integer('event_id')->default(0);
        //     $table->integer('sankakubun')->default(0);
        //     $table->integer('sex')->default(0);
        //     $table->integer('age')->default(0);
        //     $table->integer('receiptsep')->default(0);
        //     $table->string('receiptto')->nullable();
        //     $table->integer('smoke')->default(0);
        //     $table->text('misc')->nullable();
        //     $table->text('neta')->nullable();
        //     $table->string('membershipid', 20)->nullable();
        //     $table->integer('membershipsociety')->default(0);
        //     $table->string('membershipsocietyother')->nullable();
        //     $table->integer('excursion')->default(0);
        //     $table->integer('extrastaynum')->default(0);
        //     $table->text('extrastayplace')->nullable();
        //     $table->boolean('zenpaku')->default(false);
        //     $table->integer('bus')->default(0);
        //     $table->integer('bus2')->default(1);
        //     $table->integer('buskekka');
        //     $table->integer('bus2kekka');
        //     $table->integer('roomshare');
        //     $table->integer('vaccination')->default(0);
        //     $table->integer('recordofvac')->default(0);
        //     $table->integer('fee')->default(0);
        //     $table->boolean('islate')->default(false);
        //     $table->boolean('isspecial')->default(false);
        //     $table->boolean('isfamily')->default(false);
        //     $table->boolean('ispaid')->default(false);
        //     $table->string('memo')->nullable();
        //     $table->dateTime('created')->nullable();
        //     $table->dateTime('submitted')->nullable();
        //     $table->dateTime('modified')->nullable();
        //     $table->boolean('deleted')->default(false);

        //     // $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
