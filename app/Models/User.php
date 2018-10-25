<?php

namespace App\Models;

use App\Models\Drive\Folder;
use App\Models\Drive\Server;
use App\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['email_verified_at', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'folder_id' => 'integer',
        'allocated_drive_bytes' => 'integer',
        'used_drive_bytes' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'allocated_drive_bytes'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_allocated_drive_bytes',
        'formatted_used_drive_bytes',
    ];

    /**
     * Get the formatted allocated drive bytes.
     *
     * @return string   
     */
    protected function getFormattedAllocatedDriveBytesAttribute()
    {
        return Server::formatBytes($this->allocated_drive_bytes);
    }

    /**
     * Get the formatted used drive bytes.
     *
     * @return string   
     */
    protected function getFormattedUsedDriveBytesAttribute()
    {
        return Server::formatBytes($this->used_drive_bytes);
    }

    /**
     * Get only public user information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublicInfo($query)
    {
        return $query->select('id', 'first_name', 'last_name');
    }

    /**
     * Get the user's folder.
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Create the root folder for the user if it does not exist.
     */
    public function createRootFolder()
    {
        // Return the folder if it already exists
        if ($this->folder_id) {
            return $this->folder;
        }

        $folder = new Folder;
        $folder->name = $this->slug;
        $folder->owned_by_id = $this->id;
        $folder->created_by_id = $this->id;
        $folder->updated_by_id = $this->id;

        $folder->save();

        $this->folder_id = $folder->id;

        $this->save();

        return $folder;
    }
}
