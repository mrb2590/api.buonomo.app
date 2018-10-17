<?php

namespace App\Traits;

use App\Models\Role;

trait HasRoles
{
    /**
     * Get the roles that belong to the user.
     * 
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if the user has a role.
     * 
     * @param string|array $role
     * @return boolean
     */
    public function hasRole($role)
    {
        // If string is paseed
        if (is_string($role)) {
            return $this->roles->pluck('name')->contains($role);
        }
        
        // If array of roles are passed
        return !! $role->intersect($this->roles)->count();
    }

    /**
     * Check if the user does not have a role.
     * 
     * @param string|array $role
     * @return boolean
     */
    public function doesNotHaveRole($role)
    {
        return ! $this->hasRole($role);
    }

    /**
     * Assign a user a role
     * 
     * @param string $role
     */
    public function assignRole($role) {
        $this->roles()->attach(Role::whereName($role)->firstOrFail()->id);
    }

    /**
     * Assign a user a role
     * 
     * @param string $role
     */
    public function removeRole($role) {
        $this->roles()->detach(Role::whereName($role)->firstOrFail()->id);
    }
}
