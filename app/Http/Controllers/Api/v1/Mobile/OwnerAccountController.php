<?php

namespace App\Http\Controllers\Api\v1\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class OwnerAccountController extends Controller
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






    public function getManagers(Request $request){
        // Get all user that is in managers table
        $managers = Manager::with('user')->get();
        
        if ($managers->isEmpty()) {
            return response()->json(['message' => 'No managers found'], 404);
        }

        $users = $managers->pluck('user');

        return response()->json($users, 200);
    }

    public function getSingleManager(Request $request, User $manager)
    {
        if (!$manager) {
            return response()->json(['message' => 'Manager not found'], 404);
        }
        return response()->json($manager, 200);
    }

    public function createManager(Request $request){
        $validate = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create($validate);
        $manager = Manager::create([
            'user_id' => $user->id
        ]);
        return response()->json(['message' => 'Manager created successfully', 'manager' => $manager, 'authentication' => [
            'email' => $validate['email'],
            'password' => $validate['password']
        ]], 201);
    }




    public function updateManager(Request $request, User $manager){
        $validate = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $manager->id,
        ]);

        if (!$manager) {
            return response()->json(['message' => 'Manager not found'], 404);
        }

        $oldManagerData = new ($manager);
        $manager->update($validate);
        $manager->save();

        $newManagerData = $manager; 
        return response()->json([
            'message' => 'Manager details updated successfully',
            'old_data' => $oldManagerData,
            'new_data' => $newManagerData], 
            200
        );
    }

    public function changePasswordManager(Request $request, User $manager){
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!$manager) {
            return response()->json(['message' => 'Manager not found'], 404);
        }

        $manager->forceFill([
            'password' => Hash::make($request->string('password')),
            'remember_token' => Str::random(60),
        ])->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function deleteManager(Request $request, User $manager){
        if (!$manager) {
            return response()->json(['message' => 'Manager not found'], 404);
        }
    
        $manager->delete();
        return response()->json(['message' => 'Manager deleted successfully'], 200);
    }


}
