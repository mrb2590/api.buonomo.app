<?php

namespace App\Models\Drive;

use App\Models\Drive\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'drive_folders';

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
        $path = '';

        $this->recursiveForEachParent(function($folder) use (&$path) {
            $path = $folder->name.'/'.$path;
        });

        $path = '/'.$path;

        return $path;
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
     * Get the child folders.
     */
    public function folders()
    {
        return $this->hasMany(Folder::class, 'folder_id', 'id');
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
     * Move this folder to another folder.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function moveTo(Folder $folder)
    {
        // Update current parent folder size
        $this->folder->size -= $this->size;
        $this->folder->save();

        $that = $this;

        $this->recursiveForEachParent(function($folder) use ($that) {
            $folder->size -= $that->size;
        });

        // If new owner, update the owner on this and all children
        if ($this->owned_by_id !== $folder->owned_by_id) {
            // Update the original owner's drive bytes
            $this->owned_by->used_drive_bytes -= $this->size;
            $this->owned_by->save();
            
            // Update this folder's owner
            $this->owned_by_id = $folder->owned_by_id;
            $this->save();

            // Update this folder's children's owner
            $this->recursiveForEachChild(function($childFolder) use ($folder) {
                $childFolder->owned_by_id = $folder->owned_by_id;
                $childFolder->save();
            });

            // Update the new owner's used drive bytes
            $this->load('owned_by');
            $this->owned_by->used_drive_bytes += $this->size;
            $this->owned_by->save();
        }

        // Move the folder
        $this->folder_id = $folder->id;
        $this->save();

        // Update all new parent folder sizes
        $this->load('folder');
        $this->folder->size += $this->size;
        $this->folder->save();

        $that = $this;

        $this->recursiveForEachParent(function($folder) use ($that) {
            $folder->size += $that->size;
        });
    }

    /**
     * Create a zip of the folder and its contents.
     *
     * @return  string  The absolute path of the zip
     */
    public function packageZip()
    {
        $zip = new \ZipArchive;
        $filename = 'temp'.time().'.zip';
        $zipPath = config('filesystems.disks.tmp.root').'/'.$filename;

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            abort(500, 'Failed to open zip file.');
        }

        $zip->addEmptyDir(substr($this->path, 1, strlen($this->path) - 1));

        $this->recursiveForEachChild(function($folder) use (&$zip) {
            $zip->addEmptyDir(substr($folder->path, 1, strlen($folder->path) - 1));
        });

        if ($zip->close() !== true) {
            abort(500, 'Failed to save zip file.');
        }

        return $zipPath;
    }

    /**
     * Recursively traverse all parent folders.
     *
     * @param  \Closure $callback
     */
    public function recursiveForEachParent(\Closure $callback)
    {
        $callback($this);

        if ($this->folder_id !== null) {
            $folder = Folder::find($this->folder_id);

            $folder->recursiveForEachParent($callback);
        }
    }

    /**
     * Recursively traverse all child folders.
     *
     * @param  \Closure $callback
     */
    public function recursiveForEachChild(\Closure $callback)
    {
        foreach ($this->folders as $folder) {
            $callback($folder);

            $folder->recursiveForEachChild($callback);
        }
    }
}
