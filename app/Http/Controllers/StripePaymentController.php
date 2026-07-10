<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Stripe;

class StripePaymentController extends Controller
{
    /**
     * Show Stripe Payment Form
     */
    public function stripe(): View
    {
        return view('stripe');
    }

    /**
     * Process Stripe Payment
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
                "amount" => 10 * 100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Payment from " . $request->name,

                "metadata" => [
                    "customer_name" => $request->name,
                    "customer_email" => $request->email,
                ],
            ]);

            Payment::create([
                'name' => $request->name,
                'email' => $request->email,
                'amount' => 10.00,
                'currency' => 'USD',
                'stripe_charge_id' => $charge->id,
                'status' => $charge->status,
                'metadata' => $charge->toArray(),
            ]);

            return redirect()
                ->route('payment.history')
                ->with('success', 'Payment completed successfully.');

        } catch (\Exception $e) {

            return back()->withInput()->with(
                'error',
                'Payment Failed : ' . $e->getMessage()
            );
        }
    }

    /**
     * Payment History
     */
    public function history(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $totalPayments = Payment::count();

        $successPayments = Payment::where('status', 'succeeded')->count();

        $failedPayments = Payment::where('status', 'failed')->count();

        $pendingPayments = Payment::where('status', 'pending')->count();

        $totalRevenue = Payment::where('status', 'succeeded')->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Payment Listing
        |--------------------------------------------------------------------------
        */

        $payments = Payment::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $payments->where(function ($query) use ($search) {

                $query->where('id', $search)

                    ->orWhere('name', 'like', "%{$search}%")

                    ->orWhere('email', 'like', "%{$search}%")

                    ->orWhere('stripe_charge_id', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $payments->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | From Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $payments->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('to_date')) {

            $payments->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($request->sort) {

            case 'oldest':
                $payments->orderBy('created_at', 'asc');
                break;

            case 'amount_high':
                $payments->orderBy('amount', 'desc');
                break;

            case 'amount_low':
                $payments->orderBy('amount', 'asc');
                break;

            default:
                $payments->latest();
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $payments = $payments
            ->paginate(4)
            ->withQueryString();

        return view('payment-history', [

            'payments' => $payments,

            'totalPayments' => $totalPayments,

            'successPayments' => $successPayments,

            'failedPayments' => $failedPayments,

            'pendingPayments' => $pendingPayments,

            'totalRevenue' => $totalRevenue,
        ]);
    }

    /**
     * Delete Payment
     */
    public function destroy($id): RedirectResponse
    {
        $payment = Payment::findOrFail($id);

        $payment->delete();

        return redirect()
            ->route('payment.history')
            ->with('success', 'Payment deleted successfully.');
    }
}