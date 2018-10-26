<?php

namespace App\Models\Drive;

use App\Models\Drive\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'drive_files';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'folder_id' => 'integer',
        'size' => 'integer',
        'created_by_id' => 'integer',
        'owned_by_id' => 'integer',
        'updated_by_id' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['path', 'formatted_size'];

    /**
     * The relationships to always load.
     *
     * @var array
     */
    // protected $with = ['owned_by', 'created_by', 'updated_by'];

    /**
     * Get the folder path.
     *
     * @return string   
     */
    protected function getPathAttribute()
    {
        $path = $this->folder->name.'/'.$this->name.'.'.$this->extension;

        $this->folder->recursiveForEachParent(function($folder) use (&$path) {
            $path = $folder->name.'/'.$path;
        });

        unset($this->folder);

        return '/'.$path;
    }

    /**
     * Get the formatted folder size.
     *
     * @return string   
     */
    protected function getFormattedSizeAttribute()
    {
        return Server::formatBytes($this->size);
    }

    /**
     * Get the parent folder.
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    /**
     * Get the owner of the folder.
     */
    public function owned_by()
    {
        // return $this->belongsTo(User::class, 'owned_by_id')->publicInfo();
        return $this->belongsTo(User::class, 'owned_by_id');
    }

    /**
     * Get the creator of the folder.
     */
    public function created_by()
    {
        // return $this->belongsTo(User::class, 'created_by_id')->publicInfo();
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the user who last updated the folder.
     */
    public function updated_by()
    {
        // return $this->belongsTo(User::class, 'updated_by_id')->publicInfo();
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Move this file to another folder.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function moveTo(Folder $folder)
    {
        // Update all current parent folder sizes
        $this->folder->size -= $this->size;
        $this->folder->save();

        $that = $this;

        $this->folder->recursiveForEachParent(function($folder) use ($that) {
            $folder->size -= $that->size;
            $folder-save();
        });

        // If new owner, update the owner
        if ($this->owned_by_id !== $folder->owned_by_id) {
            // Update the original owner's drive bytes
            $this->owned_by->used_drive_bytes -= $this->size;
            $this->owned_by->save();

            // Update this file's owner
            $this->owned_by_id = $folder->owned_by_id;
            $this->save();

            // Update the new owner's used drive bytes
            $this->load('owned_by');
            $this->owned_by->used_drive_bytes += $this->size;
            $this->owned_by->save();
        }

        // Move the file
        $this->folder_id = $folder->id;
        $this->save();

        // Update all new parent folder sizes
        $this->load('folder');
        $this->folder->size += $this->size;
        $this->folder->save();

        $that = $this;

        $this->folder->recursiveForEachParent(function($folder) use ($that) {
            $folder->size += $that->size;
            $folder-save();
        });
    }
}
