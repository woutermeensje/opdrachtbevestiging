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
        Schema::table('confirmations', function (Blueprint $table) {
            $table->string('duration')->nullable()->after('agreement_date');
            $table->string('value_vat_type')->default('excl')->after('total_value');
            $table->text('termination_terms')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table) {
            $table->dropColumn(['duration', 'value_vat_type', 'termination_terms']);
        });
    }
};
