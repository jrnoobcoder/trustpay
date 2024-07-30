<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use App\Models\Agent;
use App\Models\PaymentLinks;
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
					'agents_count' => $totalCount,
					'total_customer' => PaymentLinks::getCustomerCount(Auth::id()),
					'total_collection' => PaymentLinks::getCollection(Auth::id())
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
			->where(function ($query) {
				$query->where('role', 'superadmin')
					  ->orWhere('role', 'admin');
			})
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

	/**
     * Login Agent and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     */
	public function updateAgent(Request $request,$id){
		$user = DB::table('users')
			->where('id', Auth::id())
			->where(function ($query) {
				$query->where('role', 'superadmin')
					  ->orWhere('role', 'admin');
			})
			->first();
		if($user){
		
			$validator = Validator::make($request->all(), [
				'name' => 'sometimes|required|string',
				'email' => 'sometimes|required|string|email|unique:users,email,' . $id,
				'phone' => 'sometimes|required|string|unique:users,phone,' . $id,
				//'role' => 'sometimes|required|string|in:agent,admin,superadmin',
				'user_status' => 'sometimes|required|string|in:active,inactive',
				'password' => 'nullable|string',
				//'added_by' => 'sometimes|required|exists:users,id',
			]); 
			if ($validator->fails()) {
				return response()->json(['error' => $validator->errors()], 400);
			}
			
			$user = User::find($id);
			if (!$user) {
				return response()->json(['error' => 'User not found'], 404);
			}
			$fieldsToUpdate = []; 
			if ($request->has('name') && $request->filled('name')) {
				$fieldsToUpdate['name'] = $request->input('name');
			}
			if ($request->has('email') && $request->filled('email')) {
				$fieldsToUpdate['email'] = $request->input('email');
			}
			if ($request->has('phone') && $request->filled('phone')) {
				$fieldsToUpdate['phone'] = $request->input('phone');
			}
			if ($request->has('status') && $request->filled('status')) {
				$fieldsToUpdate['status'] = $request->input('status');
			}
			if ($request->has('password') && $request->filled('password')) {
				$fieldsToUpdate['password'] = bcrypt($request->input('password'));
			}
			if ($request->has('user_status') && $request->filled('user_status')) {
				$status = $request->input('user_status');
				if (!in_array($status, ['active', 'inactive'])) {
					return response()->json(['error' => 'Invalid user status'], 400);
				}
				$fieldsToUpdate['user_status'] = $status;
			}

			try {
				$user->update($fieldsToUpdate);

				return response()->json(['response' => ['status' => true, 'message' => 'Agent updated successfully', 'user' => $user]]);
			} catch (ModelNotFoundException $e) {
				return response()->json(['error' => 'User not found'], 404);
			} catch (\Exception $e) {
				return response()->json(['error' => $e->getMessage()], 500);
			}
		}else{
			return response()->json(['response' => ['status' => false, 'message' => 'You are not authorized']]);
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
