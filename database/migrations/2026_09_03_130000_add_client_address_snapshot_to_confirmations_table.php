<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->string('client_street_name')->nullable()->after('client_kvk_number');
            $table->string('client_house_number', 20)->nullable()->after('client_street_name');
            $table->string('client_house_number_addition', 20)->nullable()->after('client_house_number');
            $table->string('client_postal_code', 20)->nullable()->after('client_house_number_addition');
            $table->string('client_city')->nullable()->after('client_postal_code');
            $table->string('client_country')->nullable()->after('client_city');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn([
                'client_street_name',
                'client_house_number',
                'client_house_number_addition',
                'client_postal_code',
                'client_city',
                'client_country',
            ]);
        });
    }
};
