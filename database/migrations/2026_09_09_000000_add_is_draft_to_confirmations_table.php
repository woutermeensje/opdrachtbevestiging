<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            // Automatisch opgeslagen conceptversie van /dashboard/aanmaken dat nog
            // niet bewust is verzonden. Zulke rijen blijven buiten de overzichten
            // en worden hersteld zodra de gebruiker terugkeert naar de wizard.
            $table->boolean('is_draft')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table): void {
            $table->dropColumn('is_draft');
        });
    }
};
