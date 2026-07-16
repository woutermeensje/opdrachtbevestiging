<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('title');
            $table->string('client_name');
            $table->string('client_contact_name')->nullable();
            $table->string('client_email');
            $table->string('client_kvk_number')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_value', 12, 2)->default(0);
            $table->string('value_vat_type')->default('excl');
            $table->date('valid_until')->nullable();
            $table->string('status')->default('concept');

            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_company_name')->nullable();
            $table->string('sender_kvk_number')->nullable();
            $table->string('sender_street_name')->nullable();
            $table->string('sender_house_number')->nullable();
            $table->string('sender_house_number_addition')->nullable();
            $table->string('sender_postal_code')->nullable();
            $table->string('sender_city')->nullable();
            $table->string('sender_country')->nullable();
            $table->string('sender_company_logo_path')->nullable();
            $table->string('sender_company_logo_original_name')->nullable();
            $table->string('sender_company_logo_mime_type')->nullable();

            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->string('attachment_mime_type')->nullable();

            $table->string('pdf_path')->nullable();
            $table->string('pdf_original_name')->nullable();
            $table->string('pdf_mime_type')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
