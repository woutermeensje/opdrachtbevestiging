<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmations', function (Blueprint $table) {
            $table->dropColumn('termination_terms');
        });
    }

    public function down(): void
    {
        Schema::table('confirmations', function (Blueprint $table) {
            $table->text('termination_terms')->nullable()->after('description');
        });
    }
};
