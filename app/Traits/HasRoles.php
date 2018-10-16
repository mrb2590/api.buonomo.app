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
     * Assign a user a role
     * 
     * @param string $role
     */
    public function assignRole($role) {
        $this->roles()->save(Role::whereName($role)->firstOrFail());
    }
}
