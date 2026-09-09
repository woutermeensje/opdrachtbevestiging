<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('company_trade_name')->nullable()->after('company_name');
        });

        Schema::table('confirmations', function (Blueprint $table): void {
            $table->string('sender_company_trade_name')->nullable()->after('sender_company_name');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn('sender_company_trade_name');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('company_trade_name');
        });
    }
};
