<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['display_name', 'description'];

    /**
     * Get the roles that the permissions belong to.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
