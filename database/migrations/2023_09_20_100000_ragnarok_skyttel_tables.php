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
            $table->id();
            $table->char('filename', 128)->index();
            $table->boolean('succeeded');
            $table->dateTime('FileCreatedDate')->nullable();
            $table->timestamps();
        });

        Schema::create('skyttel_transactions', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedInteger('batch_id');
        });

        Schema::create('skyttel_transaction_salesplace', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedInteger('transaction_id')->index();
            $table->integer('LineID');
            $table->integer('ActorID');
            $table->integer('Lane');
            $table->integer('DeviceType');
            $table->integer('DeviceID');
            $table->char('ValidationFile', 128);
        });

        Schema::create('skyttel_transaction_trips', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedInteger('transaction_id')->index();
            $table->char('OperatorReference', 128)->nullable();
            $table->integer('TourID')->nullable();
            $table->dateTime('Departure');
            $table->dateTime('Registered');
            $table->char('StopPlaceIDEntry', 24);
            $table->char('StopPlaceIDExit', 24);
            $table->boolean('Trailer')->default(false);
            $table->integer('SignalCode');
            $table->integer('MeasuredLength')->nullable();
            $table->integer('Margin')->nullable();
            $table->char('TariffClass', 16);
            $table->char('LPNFront', 16);
            $table->integer('NationLPNFront');
            $table->integer('OCRConfidenceFront');
            $table->char('LPNRear', 16);
            $table->integer('NationLPNRear');
            $table->integer('OCRConfidenceRear');
            $table->integer('SeqLC');
            $table->integer('SeqVideo');
        });

        Schema::create('skyttel_transaction_receipts', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedInteger('trip_id')->index();
            $table->char('Debit_Credit', 8);
            $table->char('TicketCodeCharged', 8)->nullable();
            $table->char('TicketCodeChargedToll', 8)->nullable();
            $table->char('ChargedType', 16);
            $table->char('ZeroEmission', 2);
            $table->float('FullPrice');
            $table->float('DiscountAmount');
            $table->float('ChargedGrossAmount');
            $table->float('ChargedAmountVAT');
            $table->float('ChargedAmountVATRate');
            $table->float('ChargedNetAmount');
            $table->float('FullPriceToll');
            $table->float('DiscountAmountToll');
            $table->float('ChargedGrossAmountToll');
            $table->float('ChargedAmountVATToll');
            $table->float('ChargedAmountVATRateToll');
            $table->float('ChargedNetAmountToll');
            $table->char('InformationCode');
            $table->char('IssuerIDCharged')->nullable();
            $table->float('OBUIssuerFee');
            $table->float('OBUIssuerFeeVAT');
            $table->float('OBUIssuerFeeVATRate');
            $table->dateTime('STLPostingDate');
            $table->char('STLDocumentNo', 16);
            $table->char('Origin', 32);
            $table->char('STLTicketVersionNo', 2);
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
