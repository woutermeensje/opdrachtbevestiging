<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->string('sender_role', 40)->default('opdrachtnemer')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn('sender_role');
        });
    }
};
