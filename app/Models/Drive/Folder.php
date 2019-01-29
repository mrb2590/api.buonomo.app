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
    protected $casts = ['size' => 'integer'];

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
    protected $with = ['owned_by', 'created_by', 'updated_by'];

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
     * Get the folder path.
     *
     * @return string
     */
    protected function getfilesCountAttribute()
    {
        return $total = $this->files()->count();
    }

    /**
     * Get the folder path.
     *
     * @return string
     */
    protected function getfoldersCountAttribute()
    {
        return $total = $this->folders()->count();
    }

    /**
     * Get the total number of files .
     *
     * @return string
     */
    protected function getTotalFilesCountAttribute()
    {
        $total = $this->filesCount;

        $this->recursiveForEachChild(function ($folder) use (&$total) {
            $total += $folder->filesCount;
        });

        return $total;
    }

    /**
     * Get the total number of folders .
     *
     * @return string
     */
    protected function getTotalFoldersCountAttribute()
    {
        $total = $this->foldersCount;

        $this->recursiveForEachChild(function ($folder) use (&$total) {
            $total += $folder->foldersCount;
        });

        return $total;
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
     * Get the child folders recursively.
     */
    public function folders_recursive()
    {
        return $this->folders()->with('folders_recursive');
    }

    /**
     * Get the owner of the folder.
     */
    public function owned_by()
    {
        return $this->belongsTo(User::class, 'owned_by_id');
    }

    /**
     * Get the creator of the folder.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the user who last updated the folder.
     */
    public function updated_by()
    {
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
     * @param  bool  $withTrashed
     */
    public function recursiveForEachParent(\Closure $callback, $withTrashed = false)
    {
        if ($this->folder_id !== null) {
            // Do this so the relationship is not appended to the original object
            if ($withTrashed) {
                $folder = Folder::withTrashed()->find($this->folder_id);
            } else {
                $folder = Folder::find($this->folder_id);
            }

            $callback($folder);

            $folder->recursiveForEachParent($callback, $withTrashed);
        }
    }

    /**
     * Recursively traverse all child folders.
     *
     * @param  \Closure $callback
     * @param  bool  $withTrashed
     * @param  int  $depth
     */
    public function recursiveForEachChild(\Closure $callback, $withTrashed = false, $depth = 1)
    {
        $folders = $withTrashed ? $this->folders()->withTrashed()->get() : $this->folders()->get();

        foreach ($folders as $folder) {
            $currentFolderDepth = $depth;
            $callback($folder, $depth);
            $depth++;
            $folder->recursiveForEachChild($callback, $withTrashed, $depth);
            $depth = $currentFolderDepth; // Reset depth back to current folder depth
        }
    }

    /**
     * Calculate and update the size of the folder.
     * This should be used only to fix any cached file sizes.
     */
    public function recalculateSize()
    {
        $this->timestamps = false; // Since this is a fix, do not update the timestamps
        $size = 0;

        foreach ($this->files as $file) {
            $size += $file->size;
        }

        // Recursively get the size of every file within this folder
        $this->recursiveForEachChild(function ($folder) use (&$size) {
            foreach ($folder->files as $file) {
                $size += $file->size;
            }
        });

        $this->size = $size;
        $this->save();

        $this->timestamps = true; // Set back incase this object is used later
    }

    /**
     * Recursively trash the folder.
     */
    public function trash()
    {
        // Delete all files from the folder
        foreach ($this->files as $file) {
            $file->delete();
        }

        // Recursively delete all child folders
        $this->recursiveForEachChild(function ($folder) {
            $folder->trash();
        });

        $this->delete();
    }

    /**
     * Delete the folder from storage and database.
     */
    public function permanentDelete()
    {
        // Delete all files from the folder
        foreach ($this->files()->withTrashed()->get() as $file) {
            $file->permanentDelete();
        }

        // Recursively delete all child folders
        $this->recursiveForEachChild(function ($folder) {
            $folder->permanentDelete();
        }, true);

        $this->forceDelete();
    }
}
