<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with sample Stripe payments.
     */
    public function run(): void
    {
        Payment::checkAndAddColumns();

        $samplePayments = [
            [
                'name' => 'Alexander Pierce',
                'email' => 'alex@example.com',
                'amount' => 120.00,
                'currency' => 'USD',
                'payment_intent_id' => 'pi_' . Str::random(24),
                'stripe_charge_id' => 'ch_' . Str::random(24),
                'status' => 'succeeded',
                'created_at' => now()->subMonths(3),
            ],
            [
                'name' => 'Sophia Loren',
                'email' => 'sophia@example.com',
                'amount' => 250.00,
                'currency' => 'USD',
                'payment_intent_id' => 'pi_' . Str::random(24),
                'stripe_charge_id' => 'ch_' . Str::random(24),
                'status' => 'succeeded',
                'created_at' => now()->subMonths(2),
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael@example.com',
                'amount' => 85.00,
                'currency' => 'USD',
                'payment_intent_id' => 'pi_' . Str::random(24),
                'stripe_charge_id' => 'ch_' . Str::random(24),
                'status' => 'partially_refunded',
                'refund_id' => 're_' . Str::random(24),
                'refunded_amount' => 30.00,
                'refund_status' => 'succeeded',
                'refund_reason' => 'Partial service cancellation',
                'created_at' => now()->subMonths(1),
            ],
            [
                'name' => 'Emma Watson',
                'email' => 'emma@example.com',
                'amount' => 45.00,
                'currency' => 'USD',
                'payment_intent_id' => 'pi_' . Str::random(24),
                'stripe_charge_id' => 'ch_' . Str::random(24),
                'status' => 'refunded',
                'refund_id' => 're_' . Str::random(24),
                'refunded_amount' => 45.00,
                'refund_status' => 'succeeded',
                'refund_reason' => 'Customer changed mind',
                'created_at' => now()->subDays(15),
            ],
            [
                'name' => 'Liam Neeson',
                'email' => 'liam@example.com',
                'amount' => 350.00,
                'currency' => 'USD',
                'payment_intent_id' => 'pi_' . Str::random(24),
                'stripe_charge_id' => 'ch_' . Str::random(24),
                'status' => 'succeeded',
                'created_at' => now()->subDays(4),
            ],
            [
                'name' => 'Olivia Rodrigo',
                'email' => 'olivia@example.com',
                'amount' => 60.00,
                'currency' => 'USD',
                'payment_intent_id' => 'pi_' . Str::random(24),
                'stripe_charge_id' => null,
                'status' => 'failed',
                'created_at' => now()->subDays(2),
            ],
        ];

        foreach ($samplePayments as $pay) {
            Payment::firstOrCreate(
                ['email' => $pay['email'], 'amount' => $pay['amount']],
                $pay
            );
        }
    }
}
