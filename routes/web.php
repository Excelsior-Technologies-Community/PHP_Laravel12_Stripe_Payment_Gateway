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
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Stripe Payment Routes
|--------------------------------------------------------------------------
*/

Route::get('/payment-receipt/{id}', [PaymentReceiptController::class,'download'])->name('payment.receipt');


Route::controller(StripePaymentController::class)->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Payment Form
    |--------------------------------------------------------------------------
    */
    Route::get('/stripe', 'stripe')->name('stripe');

    /*
    |--------------------------------------------------------------------------
    | Process Payment
    |--------------------------------------------------------------------------
    */
    Route::post('/stripe', 'stripePost')->name('stripe.post');

    /*
    |--------------------------------------------------------------------------
    | Payment History
    |--------------------------------------------------------------------------
    */
    Route::get('/payment-history', 'history')->name('payment.history');

    /*
    |--------------------------------------------------------------------------
    | Delete Payment
    |--------------------------------------------------------------------------
    */
    Route::delete('/payment/{id}', 'destroy')->name('payment.destroy');
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook Route
|--------------------------------------------------------------------------
*/

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::controller(StripeWebhookController::class)->group(function () {

    Route::get('/webhook-history', 'history')
        ->name('webhook.history');

});