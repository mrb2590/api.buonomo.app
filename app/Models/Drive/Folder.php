<?php

namespace App\Models\Drive;

use App\Models\Drive\File;
use App\Models\Drive\Server;
use App\Models\User;
use App\Traits\Drive\HasFolderPath;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasUuid, SoftDeletes, HasFolderPath;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

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
        $path = $this->name;

        $this->recursiveForEachParent(function ($folder) use (&$path) {
            $path = $folder->name . '/' . $path;
        });

        return '/' . $path;
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
    public function ownedBy()
    {
        // return $this->belongsTo(User::class, 'owned_by_id')->publicInfo();
        return $this->belongsTo(User::class, 'owned_by_id');
    }

    /**
     * Get the creator of the folder.
     */
    public function createdBy()
    {
        // return $this->belongsTo(User::class, 'created_by_id')->publicInfo();
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the user who last updated the folder.
     */
    public function updatedBy()
    {
        // return $this->belongsTo(User::class, 'updated_by_id')->publicInfo();
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Get the files in this folder.
     */
    public function files()
    {
        return $this->hasMany(File::class, 'folder_id', 'id');
    }

    /**
     * Move this folder to another folder.
     *
     * @param  \App\Models\Drive\Folder $folder
     */
    public function moveTo(Folder $folder)
    {
        // Update current parent folder sizes
        $size = $this->size;

        $this->recursiveForEachParent(function ($folder) use ($size) {
            $folder->size -= $size;
            $folder->save();
        });

        // If new owner, update the owner on this and all children
        if ($this->owned_by_id !== $folder->owned_by_id) {
            // Update the original owner's drive bytes
            $this->owned_by->used_drive_bytes -= $this->size;
            $this->owned_by->save();

            // Update this folder's owner
            $this->owned_by_id = $folder->owned_by_id;
            $this->save();

            // Update this folder's child folder's and file's owner
            $this->recursiveForEachChild(function ($childFolder) use ($folder) {
                $childFolder->owned_by_id = $folder->owned_by_id;
                $childFolder->save();

                foreach ($childFolder->files as $file) {
                    $file->owned_by_id = $folder->owned_by_id;
                    $file->save();
                }
            });

            // Update the new owner's used drive bytes
            $this->load('owned_by');
            $this->owned_by->used_drive_bytes += $this->size;
            $this->owned_by->save();
        }

        // Move the folder
        $this->folder_id = $folder->id;
        $this->save();

        // Update all new parents' folder sizes
        $this->load('folder');

        $this->recursiveForEachParent(function ($folder) use ($size) {
            $folder->size += $size;
            $folder->save();
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
        $filename = 'temp' . time() . '.zip';
        $zipPath = config('filesystems.disks.tmp.root') . '/' . $filename;

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            abort(500, 'Failed to open zip file.');
        }

        foreach ($this->files as $file) {
            $zip->addFile(
                storage_path('app/private' . $file->storage_path . '/' . $file->storage_basename),
                $file->getRelativePath(0, false)
            );
        }

        $this->recursiveForEachChild(function ($folder, $depth) use (&$zip) {
            $zip->addEmptyDir($folder->getRelativePath($depth - 1, false));

            foreach ($folder->files as $file) {
                $zip->addFile(
                    storage_path('app/private' . $file->storage_path . '/' . $file->storage_basename),
                    $file->getRelativePath($depth, false)
                );
            }
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
        if ($this->folder_id !== null) {
            // Do this so the relationship is not appended to the original object
            $folder = Folder::find($this->folder_id);

            $callback($folder);

            $folder->recursiveForEachParent($callback);
        }
    }

    /**
     * Recursively traverse all child folders.
     *
     * @param  \Closure $callback
     */
    public function recursiveForEachChild(\Closure $callback, $depth = 1)
    {
        foreach ($this->folders as $folder) {
            $currentFolderDepth = $depth;
            $callback($folder, $depth);
            $depth++;
            $folder->recursiveForEachChild($callback, $depth);
            $depth = $currentFolderDepth; // Reset depth back to current folder depth
        }
    }

    /**
     * Delete the folder from storage and database.
     */
    public function permanentDelete()
    {
        // Update folder owner's used drive bytes
        $this->owned_by->used_drive_bytes -= $this->size;
        $this->owned_by->save();

        $size = $this->size;

        // Update the parent folder's sizes
        $this->recursiveForEachParent(function ($folder) use ($size) {
            $folder->size -= $size;
            $folder->save();
        });

        // Delete all files from the folder
        foreach ($this->files as $file) {
            $file->forceDelete();
        }

        // Recursively delete all child folders
        $this->recursiveForEachChild(function ($folder) {
            foreach ($folder->files as $file) {
                $folder->permanentDelete();
            }
        });

        $this->forceDelete();
    }
}
