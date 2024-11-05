<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TokenBasedAuthController extends Controller
{
    // Login owner (mobile)
    public function loginOwner(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user || !$user->owner) {
            return response()->json([
                'message' => 'You are not authorized to login.'
            ], 401);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $plainTextToken = $user->createToken($request->device_name)->plainTextToken;
        return response()->json([
            'token' => $plainTextToken
        ]);
    }

    // Login for manger (desktop)
    public function loginManager(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->owner) {
            if (!$user || !$user->manager) {
                return response()->json([
                    'message' => 'You are not authorized to login.'
                ], 401);
            }
        }



        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $plainTextToken = $user->createToken($request->device_name)->plainTextToken;
        return response()->json([
            'token' => $plainTextToken
        ]);
    }

    public function user(Request $request){
        return response()->json($request->user()->only('id', 'first_name', 'last_name', 'email'));
    }

    // Logout user
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out'
        ]);
    }



}
