<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Direktorat;
use Illuminate\Auth\Access\HandlesAuthorization;

class DirektoratPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_direktorat');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Direktorat $direktorat): bool
    {
        return $user->can('view_direktorat');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_direktorat');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Direktorat $direktorat): bool
    {
        return $user->can('update_direktorat');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Direktorat $direktorat): bool
    {
        return $user->can('delete_direktorat');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_direktorat');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Direktorat $direktorat): bool
    {
        return $user->can('force_delete_direktorat');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_direktorat');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Direktorat $direktorat): bool
    {
        return $user->can('restore_direktorat');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_direktorat');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Direktorat $direktorat): bool
    {
        return $user->can('replicate_direktorat');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_direktorat');
    }
}
