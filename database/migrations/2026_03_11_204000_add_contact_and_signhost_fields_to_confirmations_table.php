<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->foreignId('contact_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('client_contact_name')->nullable()->after('client_name');
            $table->string('client_kvk_number', 8)->nullable()->after('client_email');
            $table->string('sender_name')->nullable()->after('status');
            $table->string('sender_email')->nullable()->after('sender_name');
            $table->string('signhost_transaction_id')->nullable()->after('signer_user_agent');
            $table->string('signhost_status')->nullable()->after('signhost_transaction_id');
            $table->string('signhost_file_id')->nullable()->after('signhost_status');
            $table->string('signhost_receipt_path')->nullable()->after('signhost_file_id');
            $table->string('signhost_signed_document_path')->nullable()->after('signhost_receipt_path');
            $table->timestamp('signhost_completed_at')->nullable()->after('signhost_signed_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropColumn([
                'client_contact_name',
                'client_kvk_number',
                'sender_name',
                'sender_email',
                'signhost_transaction_id',
                'signhost_status',
                'signhost_file_id',
                'signhost_receipt_path',
                'signhost_signed_document_path',
                'signhost_completed_at',
            ]);
        });
    }
};
