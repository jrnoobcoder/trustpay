<?php

namespace App\Http\Controllers;
use App\Mail\OTPMail;
use Illuminate\Http\Request;
use App\Models\OTP;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;

class ForgotPasswordController extends Controller
{
    public function sendOTP(Request $request)
    {
        //$request->validate(['email' => 'required|email|exists:users,email']);
        $validator = Validator::make($request->all(), [
				'email' => 'required|email|exists:users,email'
			]);
		if ($validator->fails()) {
				return response()->json([ 'response' => ['error' => $validator->errors()->all(), 'status' => false]], 400);
			}
        //$otp = rand(100000, 999999);
        //$expiresAt = Carbon::now()->addMinutes(10);
		
        $otp = new OTP();
		$otp->email = $request->email;
		$otp->otp = rand(100000, 999999);
		$otp->expires_at = Carbon::now()->addMinutes(10); 
		$otp->save();

        $mail = Mail::to($request->email)->send(new OTPMail($otp->otp));
		if($mail){
			return response()->json(['response' => ['message' => 'OTP sent successfully.', 'status' => true]]);
		}else{
			return response()->json(['message' => 'OTP not sent .']);
		}
	}
	
	
	
	public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|integer|min:6',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $otpEntry = OTP::where('email', $request->email)
                       ->where('otp', $request->otp)
                       ->where('expires_at', '>', Carbon::now())
                       ->first();

        if (!$otpEntry) {
            return response()->json(['response' => ['error' => 'Invalid or expired OTP.', 'status' => true]], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        OTP::where('email', $request->email)->delete();

        return response()->json(['response' => ['message' => 'Password reset successfully.', 'status' => true]]);
    }
}
