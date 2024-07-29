<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use App\Models\Agent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Validator; 

class AgentsController extends Controller
{
	/**
     * Get Agents | accessed by Admin & Superadmin 
     *
     * @param  use token 
     */
	public function index()
	{
		$user = DB::table('users')
					->where('id', Auth::id())
					->where('role', 'superadmin')
					->orWhere('role', 'admin')
					->first();

		if ($user && $user != null ) {
			$agents = User::where('added_by', Auth::id())
				->where('role', 'agent') 
				->get();
			$totalCount = $agents->count();
			if($agents){
			
				$agentResponse = [
					'agents' => $agents,
					'status' => true,
					'total_count' => $totalCount,
				];
				return response()->json(['response' => $agentResponse], 201);
			}else{
				return response()->json([
					'response' => [
						'status' => false,
						'message' => 'Agent not found',
					]	
				], 404);
			}
		} else {
			return response()->json([
				'response' => [
					'status' => false,
					'message' => 'You are not authorized to view agents',
				]
				
			], 404);
		}
	}
	
	
	/**
     * Get Agents | accessed by Admin & Superadmin 
     *
     * @param  use token 
     */
	public function getAgentById($id)
	{
		$user = DB::table('users')
					->where('id', Auth::id())
					->where('role', 'superadmin')
					->orWhere('role', 'admin')
					->first();

		if ($user && $user != null && !empty($id)) {
			$agent = User::where('added_by', Auth::id())
				->where('id', $id)
				->where('role', 'agent') 
				->first();
			
			if($agent){
				$agentResponse = [
					'id' => $agent->id,
					'name'  => $agent->name,
					'email' => $agent->email,
					'phone' => $agent->phone,
					//'password' => bcrypt($request->password),
					'role' => $agent->role,
					'added_by' => $agent->added_by,
					'created_at' => $agent->created_at,
					'updated_at' => $agent->updated_at,
					'status' => true,
					'message' => '',
				];
				return response()->json(['response' => $agentResponse], 201);
			}else{
				return response()->json([
					'response' => [
						'status' => false,
						'message' => 'Agent not found',
					]	
				], 404);
			}
		} else {
			return response()->json([
				'response' => [
					'status' => false,
					'message' => 'You are not authorized to view agents',
				]
				
			], 404);
		}
	}
	
	/**
     * Register agent by logged-in admin
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     */
	public function register(Request $request)
    {
		$user = DB::table('users')
					->where('id', Auth::id())
					->where('role', 'superadmin')
					->orWhere('role', 'admin')
					->first();
					
		if($user){
			$validator = Validator::make($request->all(), [
				'name' => 'required|string',
				'email' => 'required|string|unique:users',
				'phone' => 'required|string|unique:users',
				'password' => 'required|string',
				'c_password' => 'required|same:password'
			]);
			if ($validator->fails()) {
				return response()->json(['status' => false, 'error' => $validator->errors(), 'message' => "Provide right details"], 400);
			}
			$agent = new User([
				'name'  => $request->name,
				'email' => $request->email,
				'phone' => $request->phone,
				'password' => bcrypt($request->password),
				'role' => "agent",
				'added_by' => Auth::id(),
			]);

			if($agent->save()){
				//$agentResponse = $agent->only(['id', 'name', 'email', 'phone', 'created_at', 'status']);
				$agentResponse = [
					'id' => $agent->id,
					'name'  => $agent->name,
					'email' => $agent->email,
					'phone' => $agent->phone,
					'message' => "Agent registered successfully",
					'status' => true,
				];
				return response()->json(['response' => $agentResponse], 201);
			}
			else{
				return response()->json(['response' => ['status' => false, 'message' => 'Something went wrong' ]], 201);
			}
		}else{
			return response()->json(['error'=>'Unauthenticated']);
		}
    } 
	 
	/*Depricated*/
    public function registerAgent(Request $request)
    {
		$user = DB::table('users')
					->where('id', Auth::id())
					->where('role', 'superadmin')
					->orWhere('role', 'admin')
					->first();
					
		if($user){
			$request->validate([
				'name' => 'required|string',
				'email'=>'required|string|unique:agents',
				'phone'=>'required',
				'password'=>'required|string',
				'c_password' => 'required|same:password'
			]);
		
			$agent = new Agent([
				'name'  => $request->name,
				'email' => $request->email,
				'phone' => $request->phone,
				'password' => bcrypt($request->password),
				'added_by_admin_id' => Auth::id(),
			]);

			if($agent->save()){
				//$tokenResult = $user->createToken('apitoken');
				//$token = $tokenResult->plainTextToken;
				$agentResponse = $agent->only(['id', 'name', 'email', 'phone', 'created_at', 'status']);
				return response()->json(['message' => 'Agent registered successfully', 'agent' => $agentResponse], 201);
			}
			else{
				return response()->json(['error'=>'Provide proper details']);
			}
		}else{
			return response()->json(['error'=>'Unauthenticated']);
		}
    }
	
	
	/**
     * Login Agent and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     */

    public function login(Request $request)
    {
		
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
//return response()->json(['error' => 'Unauthorized'], 401);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

		if(Auth::guard('agent')->attempt($credentials)) {
            $agent = Auth::guard('agent')->user();
			$agentResponse = $agent->only(['id', 'name', 'email', 'phone', 'created_at', 'status']);
            $token = $agent->createToken('apitoken')->plainTextToken;

            return response()->json(['token' => $token, 'agent' => $agentResponse]);
        } else {

            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
	
	
	/**
     * Logout Agent (Revoke the token)
	 *
     * @return [string] message
     */
    public function logout(Request $request)
    {
		
        //$request->user()->tokens()->delete();
		Auth::guard('agent')->logout();
        return response()->json([
        'message' => 'Successfully logged out'
        ]);

    }
}
