<?php

namespace App\Policies;

use App\Models\DeploymentScript;
use App\Models\User;
class DeploymentScriptPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('scripts.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DeploymentScript $deploymentScript): bool
    {
        return $user->can('scripts.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('scripts.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DeploymentScript $deploymentScript): bool
    {
        return $user->can('scripts.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DeploymentScript $deploymentScript): bool
    {
        return $user->can('scripts.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DeploymentScript $deploymentScript): bool
    {
        return $user->can('scripts.update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DeploymentScript $deploymentScript): bool
    {
        return $user->can('scripts.delete');
    }
}
