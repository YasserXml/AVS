<?php

namespace App\Observers;

use App\Models\user;
use App\Notifications\NewUserRegistration;

class UserObserver
{
    /**
     * Handle the user "created" event.
     */
    public function created(user $user): void
    {
        $adminUsers = User::whereHas('roles', fn ($query) => 
        $query->whereIn('name', ['super_admin', 'admin'])
        )->get();
        
        // Notify all admins about the new user registration
        foreach ($adminUsers as $admin) {
            $admin->notify(new NewUserRegistration($user));
        }
    }

    /**
     * Handle the user "updated" event.
     */
    public function updated(user $user): void
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     */
    public function deleted(user $user): void
    {
        //
    }

    /**
     * Handle the user "restored" event.
     */
    public function restored(user $user): void
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     */
    public function forceDeleted(user $user): void
    {
        //
    }
}
