<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
	
	/**
     * Get Agents | accessed by Admin & Superadmin 
     *
     * @param  use token 
     */
	public function getAllUsers()
	{
		$user = DB::table('users')
					->where('id', Auth::id())
					->where('role', 'superadmin')
					->orWhere('role', 'admin')
					->first();

		if ($user && $user != null ) {
			$users = DB::table('users')
				->where('id', '!=', Auth::id())
				->get();
			$totalCount = $users->count();
			if($users){
				$agentResponse = [
					'agents' => $users,
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
     * Get User by ID
     *
     * @param  USER ID
     */
	public function getUserById($id)
	{
		/*$user = DB::table('users')
					->where('id', Auth::id())
					->where('role', 'superadmin')
					->orWhere('role', 'admin')
					->first();

		if ($user && $user != null && !empty($id)) {*/
			$user = User::where('id', $id)->first();
			
			if($user){
				$agentResponse = [
					'id' => $user->id,
					'name'  => $user->name,
					'email' => $user->email,
					'phone' => $user->phone,
					//'password' => bcrypt($request->password),
					'role' => $user->role,
					'added_by' => $user->added_by,
					'created_at' => $user->created_at,
					'updated_at' => $user->updated_at,
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
		/*} else {
			return response()->json([
				'response' => [
					'status' => false,
					'message' => 'You are not authorized to view agents',
				]
				
			], 404);
		}*/
	}
	
	/**
     * Register admin and create token
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
					->first();
					
		if($user && $user != null){			
			$request->validate([
				'name' => 'required|string',
				'email'=>'required|string|unique:users',
				'phone'=>'required|unique:users',
				'password'=>'required|string',
				'c_password' => 'required|same:password'
			]);

			$user = new User([
				'name'  => $request->name,
				'email' => $request->email,
				'phone' => $request->phone,
				'password' => bcrypt($request->password),
				'role' => "admin",
				
		
			]);

			if($user->save()){
				$tokenResult = $user->createToken('apitoken');
				$token = $tokenResult->plainTextToken;

				return response()->json([
				'message' => 'Successfully created user!',
				'accessToken'=> $token,
				],201);
			}
			else{
				return response()->json(['error'=>'Provide proper details']);
			}
		}else{
			return response()->json(['error'=>'You have no permission']);
		}
    }


    /**
     * Login admin and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     */

   /* public function login(Request $request)
    {
		
		$validator = Validator::make($request->all(), [
			'email' => 'required|string|email',
			'password' => 'required|string',
			'remember_me' => 'boolean'
		]);
		
		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], 400);
		}

        $credentials = request(['email','password']);
		if(Auth::attempt($credentials))
        {
			$user = $request->user();
			$tokenResult = $user->createToken('apitoken');
			$token = $tokenResult->plainTextToken;

			return response()->json(['response' => [
				'accessToken' =>$token,
				'token_type' => 'Bearer',
				'status' => true,
				'message' => 'logged in '
			]]);
		}else{
			return response()->json([
				'status' => false,
				'message' => 'Unauthorized'
			],401);
		}
        
    }
	*/
	
	
	
	
	
	
	public function login(Request $request){
		try {
			$validator = Validator::make($request->all(), [
				'email' => 'required|string|email',
				'password' => 'required|string',
				'remember_me' => 'boolean'
			]);
			
			if ($validator->fails()) {
				return response()->json([ 'response' => ['errors' => $validator->errors()->all()]], 400);
			}

			$credentials = request(['email','password']);

			if (!Auth::attempt($credentials)) {
				throw ValidationException::withMessages([
					'email' => ['The provided credentials are incorrect.'],
				]);
			}

			$user = $request->user();
			$tokenName = 'apitoken';
			//$user->tokens()->where('name', $tokenName)->where('id', '!=', $user->currentAccessToken()->id)->delete();
			$tokenResult = $user->createToken($tokenName);
			$token = $tokenResult->plainTextToken;
 
			// Fetch all user data
			$userData = User::where('email', $request->email)->first();
			
			return response()->json([
				'response' => [
					'accessToken' => $token,
					'token_type' => 'Bearer',
					'status' => true,
					'user_type' => $userData->role,
					'message' => 'logged in',
					'user' => $userData // Include user data in the response
				]
			]);

		} catch (ValidationException $e) {
			return response()->json([ 'response' => [
				'status' => false,
				'message' => $e->getMessage(),
				'errors' => $e->errors(),
			]], 422);

		} catch (\Exception $e) {
			return response()->json([ 'response' => [
				'status' => false, 
				'message' => 'Unauthorized'
			]], 401);
		}
	}

    /**
     * Update admin 
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     */
    public function update(Request $request, $id ="")
    {
		try {
			// Validate the incoming request data
			$validator = Validator::make($request->all(), [
				'name' => 'sometimes|required|string',
				'email' => 'sometimes|required|string|email|unique:users,email,' . Auth::id(),
				'phone' => 'sometimes|required|string|unique:users,phone,' . Auth::id(),
				'role' => 'sometimes|required|string|in:agent,admin,superadmin',
				'added_by' => 'sometimes|required|exists:users,id',
			]);
			if ($validator->fails()) {
				return response()->json(['error' => $validator->errors()], 400);
			} 
			// Find the user by ID
			$user = User::findOrFail(Auth::id());

			$fieldsToUpdate = [];
			if ($request->has('name')) {
				$fieldsToUpdate['name'] = $request->input('name');
			}
			if ($request->has('email')) {
				$fieldsToUpdate['email'] = $request->input('email');
			}
			if ($request->has('phone')) {
				$fieldsToUpdate['phone'] = $request->input('phone');
			}
			if ($request->has('role')) {
				$fieldsToUpdate['role'] = $request->input('role');
			}

			// Add more fields to update as needed

			// Save the updated user
			$user->update($fieldsToUpdate);

			return response()->json(['response' => ['status' => true, 'message' => 'User updated successfully', 'user' => $user]]);
		} catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            // Handle other exceptions (database errors, etc.)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Logout admin (Revoke the token)
    *
    * @return [string] message
    */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
        'message' => 'Successfully logged out'
        ]);

    }
}
