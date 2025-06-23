<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Direktoratfolder;
use Illuminate\Auth\Access\HandlesAuthorization;

class DirektoratfolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_direktoratfolder');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Direktoratfolder $direktoratfolder): bool
    {
        return $user->can('view_direktoratfolder');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_direktoratfolder');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Direktoratfolder $direktoratfolder): bool
    {
        return $user->can('update_direktoratfolder');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Direktoratfolder $direktoratfolder): bool
    {
        return $user->can('delete_direktoratfolder');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_direktoratfolder');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Direktoratfolder $direktoratfolder): bool
    {
        return $user->can('force_delete_direktoratfolder');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_direktoratfolder');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Direktoratfolder $direktoratfolder): bool
    {
        return $user->can('restore_direktoratfolder');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_direktoratfolder');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Direktoratfolder $direktoratfolder): bool
    {
        return $user->can('replicate_direktoratfolder');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_direktoratfolder');
    }
}
