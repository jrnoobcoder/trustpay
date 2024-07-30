<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\PaymentLinksController;
use App\Http\Controllers\ForgotPasswordController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendOTP']);
	Route::post('reset-password', [ForgotPasswordController::class, 'reset']);

    Route::group(['middleware' => 'auth:sanctum'], function() {
	  Route::post('register', [AuthController::class, 'register']);
      Route::get('logout', [AuthController::class, 'logout']);
      Route::get('edit/{id}', [AuthController::class, 'edit']);
      Route::post('profile/update', [AuthController::class, 'update']);
      Route::post('agent/register', [AgentsController::class, 'register']);
	  Route::post('agent/update/{id}', [AgentsController::class, 'updateAgent']);

    });
	
	//Route::post('agent/login', [AgentsController::class, 'login']);
	//Route::group(['middleware' => 'auth:agent'], function() {
		//Route::get('agent/logout', [AgentsController::class, 'logout']);
	//});

	
});

Route::group(['middleware' => 'auth:sanctum'], function() {
	Route::get('agents', [AgentsController::class, 'index']);
	Route::get('agent/{id}', [AgentsController::class, 'getAgentById']);
	Route::get('users', [AuthController::class, 'getAllUsers']);
	Route::get('user/{id}', [AuthController::class, 'getUserById']);
	Route::get('payment/list', [PaymentLinksController::class, 'getPaymentList']);
	Route::post('payment-link', [PaymentLinksController::class, 'createPaymentLink']); 
	Route::post('profile/image', [AuthController::class, 'updateProfileImage']);
	Route::post('/export/payment', [PaymentLinksController::class, 'exportData']);
}); 


Route::get('payment/success', [PaymentLinksController::class, 'paymentSuccess'])->name('payment.success');
Route::get('payment/intent', [PaymentLinksController::class, 'createPaymentIntent'])->name('payment.intent');
Route::get('payment/cancel', [PaymentLinksController::class, 'paymentCancel'])->name('payment.cancel');
Route::post('stripe/webhook', [PaymentLinksController::class, 'handleWebhook'])->name('stripe.webhook');

