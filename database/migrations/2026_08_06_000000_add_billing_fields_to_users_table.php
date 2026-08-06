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
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('trial_ends_at')->nullable()->after('email_verified_at');
            $table->string('subscription_status')->default('trialing')->after('trial_ends_at');
            $table->string('subscription_plan')->nullable()->after('subscription_status');
            $table->timestamp('subscription_started_at')->nullable()->after('subscription_plan');
            $table->timestamp('subscription_renews_at')->nullable()->after('subscription_started_at');
            $table->string('mollie_customer_id')->nullable()->index()->after('subscription_renews_at');
            $table->string('mollie_mandate_id')->nullable()->after('mollie_customer_id');
            $table->string('mollie_subscription_id')->nullable()->index()->after('mollie_mandate_id');
            $table->string('mollie_pending_payment_id')->nullable()->index()->after('mollie_subscription_id');
            $table->string('pending_subscription_plan')->nullable()->after('mollie_pending_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['mollie_customer_id']);
            $table->dropIndex(['mollie_subscription_id']);
            $table->dropIndex(['mollie_pending_payment_id']);
            $table->dropColumn([
                'trial_ends_at',
                'subscription_status',
                'subscription_plan',
                'subscription_started_at',
                'subscription_renews_at',
                'mollie_customer_id',
                'mollie_mandate_id',
                'mollie_subscription_id',
                'mollie_pending_payment_id',
                'pending_subscription_plan',
            ]);
        });
    }
};
