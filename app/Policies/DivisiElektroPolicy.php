<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DivisiElektro;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivisiElektroPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_divisi::elektro');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DivisiElektro $divisiElektro): bool
    {
        return $user->can('view_divisi::elektro');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_divisi::elektro');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DivisiElektro $divisiElektro): bool
    {
        return $user->can('update_divisi::elektro');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DivisiElektro $divisiElektro): bool
    {
        return $user->can('delete_divisi::elektro');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_divisi::elektro');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DivisiElektro $divisiElektro): bool
    {
        return $user->can('force_delete_divisi::elektro');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_divisi::elektro');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DivisiElektro $divisiElektro): bool
    {
        return $user->can('restore_divisi::elektro');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_divisi::elektro');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DivisiElektro $divisiElektro): bool
    {
        return $user->can('replicate_divisi::elektro');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_divisi::elektro');
    }
}
