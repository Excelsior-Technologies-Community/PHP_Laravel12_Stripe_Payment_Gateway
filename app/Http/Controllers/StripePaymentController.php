<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StripePaymentController extends Controller
{
    /**
     * Show stripe payment form
     */
    public function stripe(): View
    {
        return view('stripe');
    }

    /**
     * Process stripe payment
     */
    public function stripePost(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'stripeToken' => 'required',
        ]);

        Stripe\Stripe::setApiKey(config('stripe.secret'));

        try {
            $charge = Stripe\Charge::create([
                "amount" => 10 * 100, // $10 in cents
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Payment from " . $request->name,
                "metadata" => [
                    "customer_name" => $request->name,
                    "customer_email" => $request->email
                ]
            ]);

            // Store payment in database
            Payment::create([
                'name' => $request->name,
                'email' => $request->email,
                'amount' => 10.00,
                'stripe_charge_id' => $charge->id,
                'status' => $charge->status,
                'metadata' => $charge->toArray(),
            ]);

            return back()
                ->with('success', '✅ Payment successful! Charge ID: ' . $charge->id);

        } catch (\Exception $e) {
            return back()
                ->with('error', '❌ Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Show payment history
     */
    public function history(): View
    {
        $payments = Payment::orderBy('created_at', 'desc')->get();
        
        return view('payment-history', [
            'payments' => $payments
        ]);
    }
}