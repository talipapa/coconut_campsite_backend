<?php

namespace App\Http\Controllers\Api\v1\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class CaretakerAccountController extends Controller
{
    public function update(Request $request, User $user)
    {
        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validate = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id
        ]);
        
        $oldUserData = new ($user);
        $user->update($validate);
        $newUserData = new UserResource($user); 
        return response()->json([
            'message' => 'User details updated successfully',
            'old_data' => $oldUserData,
            'new_data' => $newUserData], 
            200
        );
    }

    public function changePassword(Request $request, User $user){
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->string('password')),
            'remember_token' => Str::random(60),
        ])->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
