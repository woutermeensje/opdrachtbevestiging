<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->string('sender_company_name')->nullable()->after('sender_email');
            $table->string('sender_kvk_number', 8)->nullable()->after('sender_company_name');
            $table->string('sender_street_name')->nullable()->after('sender_kvk_number');
            $table->string('sender_house_number', 20)->nullable()->after('sender_street_name');
            $table->string('sender_house_number_addition', 20)->nullable()->after('sender_house_number');
            $table->string('sender_postal_code', 20)->nullable()->after('sender_house_number_addition');
            $table->string('sender_city')->nullable()->after('sender_postal_code');
            $table->string('sender_country')->nullable()->after('sender_city');
            $table->string('sender_company_logo_path')->nullable()->after('sender_country');
            $table->string('sender_company_logo_original_name')->nullable()->after('sender_company_logo_path');
            $table->string('sender_company_logo_mime_type')->nullable()->after('sender_company_logo_original_name');
            $table->string('terms_path')->nullable()->after('sender_company_logo_mime_type');
            $table->string('terms_original_name')->nullable()->after('terms_path');
            $table->string('terms_mime_type')->nullable()->after('terms_original_name');
            $table->text('default_agreements')->nullable()->after('terms_mime_type');
            $table->string('pdf_path')->nullable()->after('quote_mime_type');
            $table->string('pdf_original_name')->nullable()->after('pdf_path');
            $table->string('pdf_mime_type')->nullable()->after('pdf_original_name');
            $table->timestamp('pdf_generated_at')->nullable()->after('pdf_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn([
                'sender_company_name',
                'sender_kvk_number',
                'sender_street_name',
                'sender_house_number',
                'sender_house_number_addition',
                'sender_postal_code',
                'sender_city',
                'sender_country',
                'sender_company_logo_path',
                'sender_company_logo_original_name',
                'sender_company_logo_mime_type',
                'terms_path',
                'terms_original_name',
                'terms_mime_type',
                'default_agreements',
                'pdf_path',
                'pdf_original_name',
                'pdf_mime_type',
                'pdf_generated_at',
            ]);
        });
    }
};
