<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->string('signer_signature_path')->nullable()->after('signer_user_agent');
            $table->string('signer_signature_mime_type')->nullable()->after('signer_signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn([
                'signer_signature_path',
                'signer_signature_mime_type',
            ]);
        });
    }
};
