<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentLinks;
use App\Models\User;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentListExport;

class PaymentLinksController extends Controller
{
	
	public function makePayment(Request $request){
		$pId = $request->query('payment_id');
		$user = PaymentLinks::where('payment_id', $pId)->first();
		//dd($user);
		if($user){
			if($user->payment_status =="pending"){
				return view('w', [
					'p_id' => $pId,
				]);
			}else if($user->payment_status =="paid"){
				return view('message', [
					'message' => "Payment Already Completed! ",
					'status' => true,
					'data' => $user
				]);
			}
		}else{
			return view('message', [
				'message' => "Invalid link! Please ask to your agent",
				'status' => false
			]);
		}
	}
	
	
	public function paySuccess(){
		
		$successMessage = session('success_message');
		//$request->session()->forget('data');
		$data = [
            'message' => $successMessage,
            // Add more data as needed
        ];
		return view('success', $data);
	}
	
	
	/**
     * Get whole payment list  | accessed by Admin & Superadmin 
     *
     * @param  use token 
     */
    public function getPaymentList(Request $request){
		$user = DB::table('users')
			->where('id', Auth::id())
			->first();
        $validated = $request->validate([
            'range' => 'string|in:previous_month,this_month,this_week,yesterday,today,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        $startDate = $endDate = null;
		$perPage = $request->get('per_page', 10);
		$query = PaymentLinks::select('id','agent_id','customer_name','customer_email','customer_phone','amount','currency','description','payment_link','payment_id','payment_status','created_at','updated_at');
		$startDate = $endDate = null;
		if ($request->range == "today") {
			$query->whereDate('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
		} elseif ($request->range == "yesterday") {
			$query->whereDate('created_at', [Carbon::now()->subDay()->startOfDay(), Carbon::now()->subDay()->endOfDay()]);
		} elseif ($request->range == "this_week") {
			$query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()->endOfDay()]);
		} elseif ($request->range == "this_month") {
			$query->whereYear('created_at', Carbon::now()->year)
				  ->whereMonth('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()->endOfDay()]);
		}elseif ($request->range == "previous_month") {
			$query->whereYear('created_at', Carbon::now()->year)
				  ->whereMonth('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()->endOfDay()]);
		}elseif ($request->range == "custom" && isset($validated['start_date']) && isset($validated['end_date'])) {
			$query->whereBetween('created_at', [Carbon::parse($validated['start_date'])->startOfDay(), Carbon::parse($validated['end_date'])->endOfDay()]);
			
		}


		if($user->role =="agent"){
			$query->where('agent_id', $user->id);
		}
		$list = $query->orderBy('created_at', 'desc')->get();
		
		$totalAmountToday = $query->clone() // Clone the query to avoid modifying the original one
                               ->whereDate('created_at', Carbon::today())
							   //->where('payment_status', 'paid')
                               ->sum('amount');
		
		
		$list = $list->map(function ($lis) {
				$lis->agent_name = $lis->agent_id ? User::getNameByAgentId($lis->agent_id) : null;
				$lis->created_at = $lis->created_at->format('d-m-Y');
				return $lis;
			});
		
		$totalAmount = $list->sum('amount'); 
		$total_customer = $list->count();
		return response()->json(['response' => ['list' => $list, 'total_amount'=> $totalAmount, 'todays_amount'=> $totalAmountToday, 'total_customer' => $total_customer, 'status' => true, 'message' => '' ]]);
	}
	
	
	/**
     * Create Payment link  | accessed by Admin & Superadmin 
     *
     * @param  use token 
     */
	public function createPaymentLink(Request $request)
	{
		$user = DB::table('users')
			->where('id', Auth::id())
			->first();
		if($user && $user->role == "agent"){	
			$validator = Validator::make($request->all(), [
				'customer_name' => 'required|string',
				'customer_email' => 'required|string|email',
				'customer_phone' => 'required',
				'amount' => 'required|numeric|min:0',
				'currency' => 'required|in:usd,eur',
			]);
			
			if ($validator->fails()) {
				return response()->json([ 'response' => ['errors' => $validator->errors(), 'message' => "All fields are required"]], 400);
			}

			$pid = $this->generatePaymentId(10);

			if(!empty($pid)){
			// Save payment link details to database
				$paymentLink = new PaymentLinks();
				$paymentLink->agent_id = $user->id;
				$paymentLink->customer_name = $request->input('customer_name');
				$paymentLink->customer_email = $request->input('customer_email');
				$paymentLink->customer_phone = $request->input('customer_phone');
				$paymentLink->amount = $request->input('amount');
				$paymentLink->currency = $request->input('currency');
				$paymentLink->description = 'Payment for ' . $request->input('customer_name');
				$paymentLink->payment_link = route('make.payment') . '?payment_id='.$pid;
				$paymentLink->client_secret = "";
				$paymentLink->payment_intent_id = "";
				$paymentLink->payment_status = "pending";
				$paymentLink->payment_id = $pid;
				$paymentLink->save();
		 
				return response()->json(['response' => [
					'payment_url' => $paymentLink->payment_link,
					'payment_id' => $pid,
					'status' => true,
					'message' => "Payment link generated!",
					]
				]);
			}else{
				return response()->json(['response' => [ 'status' => false, 'message' => "Somthing went wrong, Please try letter" ]]);
			}
		}else{
			return response()->json(['response' => [ 'status' => false, 'message' => "Only agent can generate link" ]]);
		}
	}
 
	
	
	public function paymentSuccess(Request $request)
	{
		$session_id = $request->query('session_id'); // Retrieve session_id from query parameters

		if (!$session_id) {
			return response()->json(['error' => 'Session ID is missing'], 400);
		}

		\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

		try {
			$session = \Stripe\Checkout\Session::retrieve($session_id);
			$payment_intent_id = $session->payment_intent;
			$payment_id = $session->metadata->payment_id;
			$payment_status = $session->payment_status;
			$createdTimestamp = $session->created;
			$expiresAtTimestamp = $session->expires_at;
			
			$createdDate = date('Y-m-d H:i:s', $createdTimestamp);
			$expiresAtDate = date('Y-m-d H:i:s', $expiresAtTimestamp);
			
			$paymentLink = PaymentLinks::where('payment_id', $payment_id)->first();
			if ($paymentLink) {
				$paymentLink->payment_intent_id = $payment_intent_id;
				$paymentLink->payment_status = $payment_status;
				$paymentLink->save();
			}

			// Return response with payment status and details
			return response()->json([ 
				'response' => [
					'id' => $paymentLink->id,
					'agent_id' =>$paymentLink->agent_id, 
					'customer_name' =>$paymentLink->customer_name, 
					'customer_email' =>$paymentLink->customer_email, 
					'customer_phone' =>$paymentLink->customer_phone, 
					'payment_status' => $payment_status, 
					'payment_id' => $payment_id, 
					'created' => $createdDate, 
					'expires_at' => $expiresAtDate, 
					'status' => true, 
					]
				]);

		} catch (\Stripe\Exception\ApiErrorException $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}






	
	public function paymentCancel(Request $req){
		return response()->json(['status' => 'cancel', 'response' => $req ]);
	}
	
	public function generatePaymentId($length = 8) {
		$characters = '0123456789';
		//$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
		}
		
		return $randomString;
	}
	
	
	
	/**
     * Handle incoming webhook from Stripe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        // Retrieve the request's body and parse it as JSON
        $payload = $request->getContent();

        // You can obtain the Stripe signature from the headers
        $sig_header = $request->header('Stripe-Signature');

        // Your webhook secret key
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    // Handle completed checkout session
                    break;
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    // Handle successful payment intent
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    // Handle failed payment intent
                    break;
                // Add other event handlers as needed

                default:
                    // Unexpected event type
                    return response()->json(['error' => 'Unhandled event type'], 400);
            }

            // Return a response indicating success
            return response()->json(['success' => true]);

        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Webhook signature verification failed'], 400);
        }
    }


	public function createPaymentIntent(Request $request){
		\Stripe\Stripe::setApiKey(config('services.stripe.secret'));
		//dd($request, $request->query('payment_id'));
		$pId = $request->query('payment_id');
		$payment = PaymentLinks::select('id','agent_id','customer_name','customer_email','customer_phone','amount','currency','description','payment_status','created_at','updated_at')
					->where('payment_id', $pId)->first(); 
		// $customer = \Stripe\Customer::create([
			// 'email' => 'test@gmail.com',
		// ]);
		if($payment->payment_status =="paid"){
			return response()->json(['response' => ['status' => true, 'payment_status' => $payment->payment_status ]], 200);
		}
		$paymentIntent = \Stripe\PaymentIntent::create([
			'amount' => $payment->amount * 100, // Amount in cents
			'currency' => $payment->currency,
			'payment_method_types' => ['card','paypal','link'],
			'description' => 'Payment for tEST',
			'capture_method' => 'automatic',
			'metadata' => [
				'customer_id' => $payment->id,
			]
		]);
		
		
		
		//dd($payment);
		if($paymentIntent){
			return response()->json(['response' => ['status' => true,'payment_status' => $payment->payment_status, 'intent' => $paymentIntent, 'user' => $payment ]], 200);
		}else{
			return response()->json(['response' => ['status' => false, 'intent' => 'Intent not found' ]], 200);
		}
	}




	
	public function processPayment(Request $request)
    {
		
		$paymentIntentId = $request->query('payment_intent');
//dd($paymentIntentId);
		// Confirm the PaymentIntent server-side with the payment_intent_id
		\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
		try {
			$paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
			 
			
			if ($paymentIntent->status === 'requires_confirmation') {
				$paymentIntent->confirm();
				$this->updatePaymentStatus($paymentIntent);
				//return redirect()->back()->with('success_message', 'Payment successful.');
				return view('success', [
					'success_message' => "Payment successful",
				]);
			}else if($paymentIntent->status === 'succeeded'){
				$data = $this->updatePaymentStatus($paymentIntent);
				//return redirect()->back()->with('success_message', 'Payment successful.');
				$request->session()->flash('data', $data);
				//return redirect()->route('payment.success.msg');
				return redirect()->route('payment.success.msg')->with('success_message', $data);
				return view('success', [
					'success_message' => "Payment successful",
				]);
			}else{
				$this->updatePaymentStatus($paymentIntent);
				//return redirect()->back()->with('success_message', 'Payment already confirmed.');
				return view('success', [
					'success_message' => "Payment already confirmed",
				]);
			}
		} catch (\Exception $e) {
			// Handle error
			return redirect()->back()->with('error_message', 'Payment failed: ' . $e->getMessage());
		}
	}
	
	public function updatePaymentStatus($paymentIntent){
		$paymentLink = PaymentLinks::find($paymentIntent->metadata->customer_id);
		//dd($paymentLink, $paymentIntent);
		if ($paymentLink) {
			$paymentLink->payment_intent_id = $paymentIntent->id;
			$paymentLink->client_secret = $paymentIntent->client_secret;
			$paymentLink->payment_status = "paid";
			$paymentLink->save();
			
			return $paymentLink;
		}
			
	}
	
	
	
	
	
	/**
     * Export payment list
     *
     * @param  use token 
     */
	 
	public function exportData(Request $request) 
    {
        //return Excel::download(new PaymentListExport, 'payment.xlsx');
		
		$validated = $request->validate([
            'range' => 'required|string|in:previous_month,this_month,this_week,yesterday,today,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
		
		$range = $validated['range'];
        $startDate = $endDate = null;
		
		switch ($range) {
            case 'previous_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth()->endOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::now()->subDay()->startOfDay();
                $endDate = Carbon::now()->subDay()->endOfDay();
                break;
            case 'today':
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'custom':
                $startDate = Carbon::parse($validated['start_date'])->startOfDay();
                $endDate = Carbon::parse($validated['end_date'])->endOfDay();
                break;
        }
		$export = new PaymentListExport($startDate, $endDate);
		$filename = $export->filename();
		return Excel::download($export, $filename);
    }
}
