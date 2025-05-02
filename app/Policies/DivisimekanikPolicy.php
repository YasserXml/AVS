<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Divisimekanik;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivisimekanikPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_divisimekanik');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Divisimekanik $divisimekanik): bool
    {
        return $user->can('view_divisimekanik');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_divisimekanik');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Divisimekanik $divisimekanik): bool
    {
        return $user->can('update_divisimekanik');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Divisimekanik $divisimekanik): bool
    {
        return $user->can('delete_divisimekanik');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_divisimekanik');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Divisimekanik $divisimekanik): bool
    {
        return $user->can('force_delete_divisimekanik');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_divisimekanik');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Divisimekanik $divisimekanik): bool
    {
        return $user->can('restore_divisimekanik');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_divisimekanik');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Divisimekanik $divisimekanik): bool
    {
        return $user->can('replicate_divisimekanik');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_divisimekanik');
    }
}
