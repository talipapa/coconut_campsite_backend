<?php

namespace App\Policies;

use App\Models\Manager;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

class TransactionPolicy
{
    public function viewAnyAdmin(User $user): Response
    {
        return $user->admin ? Response::allow() : Response::deny('You are not authorized to view transactions');
    }

    public function viewAnyCampsiteTransaction(User $user): Response
    {
        return $user->campManager ? Response::allow() : Response::deny('You are not authorized to view transactions');
    }


    // Admin should be able to view all transactions
    public function viewAny(User $user): Response
    {
        return $user->admin ? Response::allow() : Response::deny('You are not authorized to view transactions');
    }
    public function view(User $user, Transaction $transaction): Response
    {
        return $user->admin ? Response::allow() : Response::deny('You are not authorized to view transactions');
    }


    // User should be able to create a transaction
    //
    public function create(User $user): bool
    {
        return true;
    }

    // All should not be able to edit transaction
    //
    public function update(User $user, Transaction $transaction): Response
    {
        return $user->admin ? Response::allow() : Response::deny('You are not authorized to view transactions');

    }

    // All should not be able to delete transaction
    //
    public function delete(User $user, Transaction $transaction): Response
    {
        return $user->admin ? Response::allow() : Response::deny('You are not authorized to view transactions');
    }

    // All should not be able to restore transaction
    //
    // public function restore(User $user, Transaction $transaction): bool
    // {
    //     //
    // }

    // All should not be able to permanently delete transaction
    //
    public function forceDelete(User $user, Transaction $transaction): Response
    {
        return $user->admin ? Response::allow() : Response::deny('You are not authorized to view transactions');

    }
}
