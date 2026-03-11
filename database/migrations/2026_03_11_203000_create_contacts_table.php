<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('kvk_number', 8);
            $table->string('street_name')->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('house_number_addition', 20)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('contact_email');
            $table->timestamps();

            $table->index(['user_id', 'company_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
