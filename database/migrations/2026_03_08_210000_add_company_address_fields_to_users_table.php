<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('kvk_number', 8)->nullable()->after('company_name');
            $table->string('street_name')->nullable()->after('kvk_number');
            $table->string('house_number', 20)->nullable()->after('street_name');
            $table->string('house_number_addition', 20)->nullable()->after('house_number');
            $table->string('postal_code', 20)->nullable()->after('house_number_addition');
            $table->string('city')->nullable()->after('postal_code');
            $table->string('country')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'kvk_number',
                'street_name',
                'house_number',
                'house_number_addition',
                'postal_code',
                'city',
                'country',
            ]);
        });
    }
};
