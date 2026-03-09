<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->string('public_token')->nullable()->unique()->after('description');
            $table->timestamp('viewed_at')->nullable()->after('sent_at');
            $table->string('signer_name')->nullable()->after('signed_at');
            $table->string('signer_ip', 45)->nullable()->after('signer_name');
            $table->text('signer_user_agent')->nullable()->after('signer_ip');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn([
                'public_token',
                'viewed_at',
                'signer_name',
                'signer_ip',
                'signer_user_agent',
            ]);
        });
    }
};
