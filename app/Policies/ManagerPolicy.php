<?php

namespace App\Policies;

use App\Models\Manager;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

class ManagerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Manager $manager): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        $fetchAdminDetails = DB::table('admins')->where('user_id', $user->id)->first();
        if (!$fetchAdminDetails) {
            return Response::deny('Action not authorized.');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Manager $manager): Response
    {
        
        $fetchAdminDetails = DB::table('admins')->where('user_id', $user->id)->first();

        if ($fetchAdminDetails !== null) {
            return Response::allow();
        }
        if ($user-> id !== $manager->user_id) {
            return Response::deny('Action not authorized.');
        }

        
        
        return Response::allow();
    }
    
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Manager $manager): Response
    {
        $fetchAdminDetails = DB::table('admins')->where('user_id', $user->id)->first();
    
        if ($fetchAdminDetails !== null) {
            return Response::allow();
        }
        if ($user-> id !== $manager->user_id) {
            return Response::deny('Action not authorized.');
        }

        return Response::allow();
        
    }
    
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Manager $manager): Response
    {
        $fetchAdminDetails = DB::table('admins')->where('user_id', $user->id)->first();
    
        if ($fetchAdminDetails !== null) {
            return Response::allow();
        }
        if ($user-> id !== $manager->user_id) {
            return Response::deny('Action not authorized.');
        }    
        return Response::allow();
    }
    
    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Manager $manager): Response
    {
        $fetchAdminDetails = DB::table('admins')->where('user_id', $user->id)->first();

        if ($fetchAdminDetails !== null) {
            return Response::allow();
        }
        if ($user-> id !== $manager->user_id) {
            return Response::deny('Action not authorized.');
        }    
        return Response::allow();
    }
}
