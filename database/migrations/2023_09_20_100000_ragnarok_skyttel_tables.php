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
        Schema::create('skyttel_transaction_batches', function (Blueprint $table)
        {
            $table->id()->comment('Unique Ragnarok ID');
            $table->char('filename', 128)->index()->comment('Skyttel internal mechanism for file involved');
            $table->boolean('succeeded')->comment('Always 1, apparently');
            $table->dateTime('FileCreatedDate')->nullable();
            $table->timestamps();
        });

        Schema::create('skyttel_transactions', function (Blueprint $table)
        {
            $table->id()->comment('Unique Ragnarok ID per transaction');
            $table->unsignedInteger('batch_id')->comment('References `skyttel_transaction_batches.id`');
        });

        Schema::create('skyttel_transaction_salesplace', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedInteger('transaction_id')->index()->comment('References `skyttel_transactions.id`');
            $table->integer('LineID')->comment('Skyttel internal ID of operated line?');
            $table->integer('ActorID')->comment('Unknown');
            $table->integer('Lane')->comment('Unknown');
            $table->integer('DeviceType')->comment('Probably reference to skyttels internal table for device types');
            $table->integer('DeviceID')->comment('Probably Skyttels internal ID of specific device used at salesplace');
            $table->char('ValidationFile', 128)->comment('Skyttel internal file?');
        });

        Schema::create('skyttel_transaction_trips', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedInteger('transaction_id')->index()->comment('References `skyttel_transactions.id`');
            $table->char('OperatorReference', 128)->nullable()->comment('UUID-alike ID. Probably skyttel internal');
            $table->integer('TourID')->nullable()->comment('Unknown source. Possibly Skyttels internal take on tour ID');
            $table->dateTime('Departure')->comment('Scheduled or actual time?');
            $table->dateTime('Registered')->comment('Timestamp of when vehicle was registered');
            $table->char('StopPlaceIDEntry', 24)->comment('Skyttel internal ID of from port?');
            $table->char('StopPlaceIDExit', 24)->comment('Skyttel internal ID of destination port?');
            $table->boolean('Trailer')->default(false);
            $table->integer('SignalCode')->nullable();
            $table->integer('MeasuredLength')->nullable();
            $table->integer('Margin')->nullable();
            $table->char('TariffClass', 16)->nullable();
            $table->char('LPNFront', 16)->nullable()->comment('Licence plate number in front');
            $table->integer('NationLPNFront')->nullable()->comment('Nationality of licence plate in front. ID is probably skyttel internal');
            $table->integer('OCRConfidenceFront')->default(0)->comment('Optical character recognition read quality of licence plate in front');
            $table->char('LPNRear', 16)->nullable()->comment('Rear licence plate number');
            $table->integer('NationLPNRear')->nullable()->comment('Nationality of rear licence plate. Skyttel internal country ID, probably');
            $table->integer('OCRConfidenceRear')->default(0)->comment('Optical character recognition read quality of rear licence plate');
            $table->integer('SeqLC')->comment('Sequence Lane Controller');
            $table->integer('SeqVideo')->comment('Video sequence number');
        });

        Schema::create('skyttel_transaction_receipts', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedInteger('trip_id')->index()->comment('References `skyttel_transaction_trips`');
            $table->char('Debit_Credit', 8)->comment('Always `D`?');
            $table->char('TicketCodeCharged', 8)->nullable()->comment('Unknown. Example values are "AP1" and "MC"');
            $table->char('TicketCodeChargedToll', 8)->nullable()->comment('Unknown. Example values are "AP1" and "MC"');
            $table->char('ChargedType', 16)->comment('Unknown. Example values are "STL" and "EASYGO"');
            $table->char('ZeroEmission', 2)->comment('Y/N. Vehicle is electric or otherwise zero emission.');
            $table->float('FullPrice')->comment('Full price of trip');
            $table->float('DiscountAmount')->comment('Discount from full price for customer');
            $table->float('ChargedGrossAmount')->comment('The actual price charged from customer');
            $table->float('ChargedAmountVAT')->comment('Amount of tax charged');
            $table->float('ChargedAmountVATRate')->comment('Tax rate of charged amount');
            $table->float('ChargedNetAmount')->comment('Final amount in Skyttel\'s account');
            $table->float('FullPriceToll')->comment('Not used?');
            $table->float('DiscountAmountToll');
            $table->float('ChargedGrossAmountToll');
            $table->float('ChargedAmountVATToll');
            $table->float('ChargedAmountVATRateToll');
            $table->float('ChargedNetAmountToll');
            $table->char('InformationCode')->comment('Skyttel internal ID of some sort?');
            $table->char('IssuerIDCharged')->nullable()->comment('ID of issuer. 6 digit hex value, often `999999` or null');
            $table->float('OBUIssuerFee')->comment('Always 0.0, it seems, i.e. not used');
            $table->float('OBUIssuerFeeVAT');
            $table->float('OBUIssuerFeeVATRate');
            $table->dateTime('STLPostingDate');
            $table->char('STLDocumentNo', 16)->comment('Sample values: `GRATIS`, `A1133053`, `IKKEFØRT`');
            $table->char('Origin', 32)->comment('Always `OPERATOR` it seems');
            $table->char('STLTicketVersionNo', 2)->comment('Varies between 1-7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skyttel_transaction_batches');
        Schema::dropIfExists('skyttel_transactions');
        Schema::dropIfExists('skyttel_transaction_salesplace');
        Schema::dropIfExists('skyttel_transaction_trips');
        Schema::dropIfExists('skyttel_transaction_receipts');
    }
};
