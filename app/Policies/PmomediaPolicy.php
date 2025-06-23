<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pmomedia;
use Illuminate\Auth\Access\HandlesAuthorization;

class PmomediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pmomedia');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pmomedia $pmomedia): bool
    {
        return $user->can('view_pmomedia');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_pmomedia');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pmomedia $pmomedia): bool
    {
        return $user->can('update_pmomedia');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pmomedia $pmomedia): bool
    {
        return $user->can('delete_pmomedia');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_pmomedia');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Pmomedia $pmomedia): bool
    {
        return $user->can('force_delete_pmomedia');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_pmomedia');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Pmomedia $pmomedia): bool
    {
        return $user->can('restore_pmomedia');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_pmomedia');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Pmomedia $pmomedia): bool
    {
        return $user->can('replicate_pmomedia');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_pmomedia');
    }
}
