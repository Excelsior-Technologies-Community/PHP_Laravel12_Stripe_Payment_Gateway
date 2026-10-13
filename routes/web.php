<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\PaymentReceiptController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('payment.history');
});

/*
|--------------------------------------------------------------------------
| Stripe Payment Routes
|--------------------------------------------------------------------------
*/

Route::get('/payment-receipt/{id}', [PaymentReceiptController::class, 'download'])->name('payment.receipt');

Route::controller(StripePaymentController::class)->group(function () {
    // Payment Form (Modern Elements Studio)
    Route::get('/stripe', 'stripe')->name('stripe');

    // Create PaymentIntent (Modern API)
    Route::post('/stripe/create-payment-intent', 'createPaymentIntent')->name('stripe.payment-intent');

    // Confirm Payment
    Route::post('/stripe/confirm-payment', 'confirmPayment')->name('stripe.confirm-payment');

    // Legacy Charge Submission
    Route::post('/stripe', 'stripePost')->name('stripe.post');

    // 1-Click Refund Engine (Full / Partial)
    Route::post('/payment/{id}/refund', 'refund')->name('payment.refund');

    // Payment History & Analytics Studio
    Route::get('/payment-history', 'history')->name('payment.history');

    // Delete Payment
    Route::delete('/payment/{id}', 'destroy')->name('payment.destroy');
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook Routes
|--------------------------------------------------------------------------
*/

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::controller(StripeWebhookController::class)->group(function () {
    Route::get('/webhook-history', 'history')->name('webhook.history');
});