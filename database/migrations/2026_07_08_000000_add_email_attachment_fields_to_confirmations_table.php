<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->string('attachment_path')->nullable()->after('sender_email');
            $table->string('attachment_original_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime_type')->nullable()->after('attachment_original_name');
            $table->string('quote_path')->nullable()->after('attachment_mime_type');
            $table->string('quote_original_name')->nullable()->after('quote_path');
            $table->string('quote_mime_type')->nullable()->after('quote_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn([
                'attachment_path',
                'attachment_original_name',
                'attachment_mime_type',
                'quote_path',
                'quote_original_name',
                'quote_mime_type',
            ]);
        });
    }
};
