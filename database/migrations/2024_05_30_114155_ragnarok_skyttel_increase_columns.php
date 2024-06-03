<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Increase size of most char columns to default length.
 *
 * The tuned sizes of char colums based on available data was prone to import
 * errors from various chunks. This migration tries to be a lot less restrictive
 * to fitting data to columns by bumping char columns to default 255 characters.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skyttel_transaction_trips', function (Blueprint $table) {
            $table->char('StopPlaceIDEntry')->comment('Skyttel internal ID of from port?')->change();
            $table->char('StopPlaceIDExit')->comment('Skyttel internal ID of destination port?')->change();
            $table->char('TariffClass')->nullable()->change();
            $table->char('LPNFront')->nullable()->comment('Licence plate number in front')->change();
            $table->char('LPNRear')->nullable()->comment('Rear licence plate number')->change();
        });
        Schema::table('skyttel_transaction_receipts', function (Blueprint $table) {
            $table->char('Debit_Credit')->change()->comment('Always `D`?');
            $table->char('TicketCodeCharged')->nullable()->comment('Unknown. Example values are "AP1" and "MC"')->change();
            $table->char('TicketCodeChargedToll')->nullable()->comment('Unknown. Example values are "AP1" and "MC"')->change();
            $table->char('ChargedType')->comment('Unknown. Example values are "STL" and "EASYGO"')->change();
            $table->char('ZeroEmission')->comment('Y/N. Vehicle is electric or otherwise zero emission.')->change();
            $table->char('STLDocumentNo')->comment('Sample values: `GRATIS`, `A1133053`, `IKKEFØRT`')->change();
            $table->char('Origin')->comment('Always `OPERATOR` it seems')->change();
            $table->char('STLTicketVersionNo')->comment('Varies between 1-7')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
