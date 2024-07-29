<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\PaymentLinksController;
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
    Route::post('register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'auth:sanctum'], function() {
		
      Route::get('logout', [AuthController::class, 'logout']);
      Route::post('update', [AuthController::class, 'update']);
      Route::post('agent/register', [AgentsController::class, 'register']);
	  //Route::get('agent/logout', [AgentsController::class, 'logout']);

    });
	
	Route::post('agent/login', [AgentsController::class, 'login']);
	//Route::group(['middleware' => 'auth:agent'], function() {
		//Route::get('agent/logout', [AgentsController::class, 'logout']);
	//});

	
});

Route::group(['middleware' => 'auth:sanctum'], function() {
	Route::get('agents', [AgentsController::class, 'index']);
	Route::get('agent/{id}', [AgentsController::class, 'getAgentById']);
	Route::get('users', [AuthController::class, 'getAllUsers']);
	Route::get('user/{id}', [AuthController::class, 'getUserById']);
	
});

Route::post('payment-link', [PaymentLinksController::class, 'createPaymentLink']);
Route::get('payment/success', [PaymentLinksController::class, 'paymentSuccess'])->name('payment.success');
Route::get('payment/cancel', [PaymentLinksController::class, 'paymentCancel'])->name('payment.cancel');
Route::post('stripe/webhook', [PaymentLinksController::class, 'handleWebhook'])->name('stripe.webhook');