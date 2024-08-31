<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    // Sign up method
    public function signup(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'userfullname' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create user
        $user = User::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'name' => $request->userfullname,
            'location' => $request->location,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 201);
    }

    // Login method
    public function login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check credentials
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    // Logout method
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function changeRole(Request $request)
    {
        // Authorization: Only admin can change roles (adjust as needed)
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'new_role' => 'required|in:organization,individual',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the user and update the role
        $user = User::find($request->user_id);
        $user->role = $request->new_role;
        $user->save();

        return response()->json(['message' => 'Role updated successfully', 'role' => $user->role], 200);
    }
}
