<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('company_logo_path')->nullable()->after('country');
            $table->string('company_logo_original_name')->nullable()->after('company_logo_path');
            $table->string('company_logo_mime_type')->nullable()->after('company_logo_original_name');
            $table->string('terms_path')->nullable()->after('company_logo_mime_type');
            $table->string('terms_original_name')->nullable()->after('terms_path');
            $table->string('terms_mime_type')->nullable()->after('terms_original_name');
            $table->text('default_agreements')->nullable()->after('terms_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'company_logo_path',
                'company_logo_original_name',
                'company_logo_mime_type',
                'terms_path',
                'terms_original_name',
                'terms_mime_type',
                'default_agreements',
            ]);
        });
    }
};
