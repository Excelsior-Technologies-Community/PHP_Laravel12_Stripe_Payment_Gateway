<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentWebhook;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\View\View;

class StripeWebhookController extends Controller
{


    public function handle(Request $request)
    {
        \Log::info('Stripe webhook called');


        $payload = $request->getContent();

        $signature = $request->header('Stripe-Signature');


        \Log::info('Stripe Signature: ' . $signature);


        try {

            $event = Webhook::constructEvent(

                $payload,

                $signature,

                config('stripe.webhook_secret')

            );
        } catch (\Exception $e) {


            \Log::error('Webhook Error: ' . $e->getMessage());


            return response()->json([

                'error' => $e->getMessage()

            ], 400);
        }


        \Log::info('Event Type: ' . $event->type);



        PaymentWebhook::firstOrCreate(

            [
                'event_id' => $event->id
            ],

            [
                'event_type' => $event->type,
                'payload' => $event->data->object,
                'status' => 'received'
            ]

        );


        return response()->json([

            'status' => 'success'

        ]);
    }

    /**
     * Show Stripe Webhook History
     */
    public function history(Request $request): View
    {

        $webhooks = PaymentWebhook::query();


        // Search by event id or event type
        if ($request->filled('search')) {

            $search = $request->search;

            $webhooks->where(function ($query) use ($search) {

                $query->where('event_id', 'like', "%{$search}%")
                    ->orWhere('event_type', 'like', "%{$search}%");
            });
        }


        // Filter by event type
        if ($request->filled('type')) {

            $webhooks->where('event_type', $request->type);
        }


        $webhooks = $webhooks
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();



        $eventTypes = PaymentWebhook::select('event_type')
            ->distinct()
            ->pluck('event_type');


        return view('webhook-history', [

            'webhooks' => $webhooks,

            'eventTypes' => $eventTypes

        ]);
    }
}
