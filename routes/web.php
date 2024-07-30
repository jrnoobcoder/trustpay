<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentLinksController;
use Stripe;
use Stripe\PaymentIntent;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

		$paymentIntent = \Stripe\PaymentIntent::create([
			'amount' => 25 * 100, // Amount in cents
			'currency' => 'usd',
			'payment_method_types' => ['card','paypal'],
			'description' => 'Payment for tEST',
			'capture_method' => 'automatic',
		]);
		//dd($paymentIntent);
		return view('w', [
            'intent' => $paymentIntent,
        ]);
});
//Route::post('payment/process', [PaymentLinksController::class, 'processPayment'])->name('payment.process');
Route::get('payment/process', [PaymentLinksController::class, 'processPayment'])->name('payment.process');
Route::get('pay', [PaymentLinksController::class, 'makePayment'])->name('make.payment');
Route::get('payment/success', [PaymentLinksController::class, 'paySuccess'])->name('payment.success.msg');
