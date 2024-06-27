<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes non-critical columns that otherwise just takes up space.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skyttel_transaction_salesplace', function (Blueprint $table) {
            $table->integer('ActorID')->comment('Operator ID. 101010: Norled, 101677: Torghatten Nord, 101678: Boreal')->change();
            $table->dropColumn([
                'Lane',
                'DeviceType',
                'DeviceID',
                'ValidationFile',
            ]);
        });
        Schema::table('skyttel_transaction_trips', function (Blueprint $table) {
            $table->dropColumn([
                'SignalCode',
                'MeasuredLength',
                'Margin',
                'SeqLC',
                'SeqVideo',
            ]);
        });
        Schema::table('skyttel_transaction_receipts', function (Blueprint $table) {
            $table->char('TicketCodeCharged', 8)->nullable()->comment('AutoPass code. AP1-9: Classified vehicle length. MC: Motorcycle')->change();
            $table->char('TicketCodeChargedToll', 8)->nullable()->comment('AutoPass code. AP1-9: Classified vehicle length. MC: Motorcycle')->change();
            $table->dropColumn([
                'InformationCode',
                'IssuerIDCharged',
                'Origin',
                'STLTicketVersionNo',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skyttel_transaction_salesplace', function (Blueprint $table)
        {
            $table->integer('Lane')->after('ActorID')->comment('Unknown');
            $table->integer('DeviceType')->after('Lane')->comment('Probably reference to skyttels internal table for device types');
            $table->integer('DeviceID')->after('DeviceType')->comment('Probably Skyttels internal ID of specific device used at salesplace');
            $table->char('ValidationFile', 128)->after('DeviceID')->comment('Skyttel internal file?');
        });
        Schema::table('skyttel_transaction_trips', function (Blueprint $table)
        {
            $table->integer('SignalCode')->after('Trailer')->nullable();
            $table->integer('MeasuredLength')->after('SignalCode')->nullable();
            $table->integer('Margin')->after('MeasuredLength')->nullable();
            $table->integer('SeqLC')->after('OCRConfidenceRear')->comment('Sequence Lane Controller');
            $table->integer('SeqVideo')->after('SeqLC')->comment('Video sequence number');
        });
        Schema::table('skyttel_transaction_receipts', function (Blueprint $table)
        {
            $table->char('InformationCode')->after('ChargedNetAmountToll')->comment('Skyttel internal ID of some sort?');
            $table->char('IssuerIDCharged')->after('InformationCode')->nullable()->comment('ID of issuer. 6 digit hex value, often `999999` or null');
            $table->char('Origin', 32)->after('STLDocumentNo')->comment('Always `OPERATOR` it seems');
            $table->char('STLTicketVersionNo', 2)->after('Origin')->comment('Varies between 1-7');
        });
    }
};
