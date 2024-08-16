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
        Schema::table('skyttel_transaction_batches', function (Blueprint $table) {
            $table->dropIndex('skyttel_transaction_batches_filename_index');
            $table->char('filename')->index()->comment('Skyttel internal mechanism for file involved')->change();
        });

        Schema::table('skyttel_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')->comment('References `skyttel_transaction_batches.id`')->change();
        });

        Schema::table('skyttel_transaction_salesplace', function (Blueprint $table) {
            $table->dropIndex('skyttel_transaction_salesplace_transaction_id_index');

            $table->unsignedBigInteger('transaction_id')->index()->comment('References `skyttel_transactions.id`')->change();
            $table->bigInteger('LineID')->comment('Skyttel internal ID of operated line?')->change();
            $table->bigInteger('ActorID')->comment('Unknown')->change();
        });

        Schema::table('skyttel_transaction_trips', function (Blueprint $table) {
            $table->dropIndex('skyttel_transaction_trips_transaction_id_index');

            $table->unsignedBigInteger('transaction_id')->index()->comment('References `skyttel_transactions.id`')->change();
            $table->char('OperatorReference', 128)->nullable()->comment('UUID-alike ID. Probably skyttel internal')->change();
            $table->bigInteger('TourID')->nullable()->comment('Unknown source. Possibly Skyttels internal take on tour ID')->change();
            $table->bigInteger('NationLPNFront')->nullable()->comment('Nationality of licence plate in front. ID is probably skyttel internal')->change();
            $table->bigInteger('OCRConfidenceFront')->default(0)->comment('Optical character recognition read quality of licence plate in front')->change();
            $table->bigInteger('NationLPNRear')->nullable()->comment('Nationality of rear licence plate. Skyttel internal country ID, probably')->change();
            $table->bigInteger('OCRConfidenceRear')->default(0)->comment('Optical character recognition read quality of rear licence plate')->change();
        });

        Schema::table('skyttel_transaction_receipts', function (Blueprint $table) {
            $table->dropIndex('skyttel_transaction_receipts_trip_id_index');

            $table->unsignedBigInteger('trip_id')->index()->comment('References `skyttel_transaction_trips`')->change();
            $table->double('FullPrice')->comment('Full price of trip')->change();
            $table->double('DiscountAmount')->comment('Discount from full price for customer')->change();
            $table->double('ChargedGrossAmount')->comment('The actual price charged from customer')->change();
            $table->double('ChargedAmountVAT')->comment('Amount of tax charged')->change();
            $table->double('ChargedAmountVATRate')->comment('Tax rate of charged amount')->change();
            $table->double('ChargedNetAmount')->comment('Final amount in Skyttel\'s account')->change();
            $table->double('FullPriceToll')->comment('Not used?')->change();
            $table->double('DiscountAmountToll')->change();
            $table->double('ChargedGrossAmountToll')->change();
            $table->double('ChargedAmountVATToll')->change();
            $table->double('ChargedAmountVATRateToll')->change();
            $table->double('ChargedNetAmountToll')->change();
            $table->double('OBUIssuerFee')->comment('Always 0.0, it seems, i.e. not used')->change();
            $table->double('OBUIssuerFeeVAT')->change();
            $table->double('OBUIssuerFeeVATRate')->change();
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
