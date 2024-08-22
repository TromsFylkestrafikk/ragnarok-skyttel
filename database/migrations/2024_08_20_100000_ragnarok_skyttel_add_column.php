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
        Schema::table('skyttel_transaction_receipts', function (Blueprint $table) {
            $table->char('InformationCode')->after('ChargedNetAmountToll')->comment('Skyttel internal ID of some sort?');
            $table->char('IssuerIDCharged')->nullable()->after('InformationCode')->comment('ID of issuer. 6 digit hex value, often `999999` or null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skyttel_transaction_receipts', function (Blueprint $table) {
            $table->dropColumn([
                'InformationCode',
                'IssuerIDCharged',
            ]);
        });
    }
};
