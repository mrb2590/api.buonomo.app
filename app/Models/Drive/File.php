<?php

namespace App\Models\Drive;

use App\Models\Drive\Folder;
use App\Models\Drive\Server;
use App\Models\User;
use App\Traits\Drive\HasFolderPath;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
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
    protected $table = 'drive_files';

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
        $path = $this->folder->name . '/' . $this->name . '.' . $this->extension;

        $this->folder->recursiveForEachParent(function ($folder) use (&$path) {
            $path = $folder->name . '/' . $path;
        });

        unset($this->folder);

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
     * Move this file to another folder.
     *
     * @param  \App\Models\Drive\Folder $folder
     */
    public function moveTo(Folder $folder)
    {
        // Update all current parent folder sizes
        $this->folder->size -= $this->size;
        $this->folder->save();

        $size = $this->size;

        $this->folder->recursiveForEachParent(function ($folder) use ($size) {
            $folder->size -= $size;
            $folder->save();
        });

        // If new owner, update the owner
        if ($this->owned_by_id !== $folder->owned_by_id) {
            // Update the current owner's drive bytes
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

        $size = $this->size;

        $this->folder->recursiveForEachParent(function ($folder) use ($size) {
            $folder->size += $size;
            $folder->save();
        });
    }

    /**
     * Save file to the file system.
     *
     * @param  \Illuminate\Http\UploadedFile $file
     * @param  \App\Models\Drive\Folder $parent
     * @param  \App\Models\User $user
     * @return bool
     */
    public function saveToStorage(UploadedFile $uploadedFile, Folder $parent, User $user)
    {
        // Need to create the filename ourselves because Laravel will not always use the correct
        // extension
        $filename = Str::random(40) . '.' . $uploadedFile->getClientOriginalExtension();

        $date = Carbon::now();
        $storagePath = $date->format('Y') . '/' . $date->format('m') . '/' . $date->format('d');

        // Store file
        $path = Storage::disk('private')
            ->putFileAs($storagePath, $uploadedFile, $filename);

        $pathInfo = pathinfo($path);
        $name = pathinfo($uploadedFile->getClientOriginalName())['filename'];

        $this->extension = strtolower($pathInfo['extension']);
        $this->storage_filename = $pathInfo['filename'];
        $this->storage_basename = $pathInfo['basename'];
        $this->storage_path = '/' . $storagePath;
        $this->mime_type = $uploadedFile->getMimeType();
        $this->size = Storage::disk('private')->size($path);
        $this->folder_id = $parent->id;
        $this->owned_by_id = $user->id;
        $this->created_by_id = $user->id;
        $this->updated_by_id = $user->id;
        $this->name = $name;

        $newUserDriveBytes = $parent->owned_by->used_drive_bytes + $this->size;

        // Make sure owner has enough allocated storage (incase client size was incorrect)
        if ($newUserDriveBytes > $parent->owned_by->allocated_drive_bytes) {
            Storage::disk('private')->delete($path);
            return false;
        }

        // Append number to filenames if it's is already used in a folder
        $i = 0;
        $existingFile = true;

        while ($existingFile) {
            // Check if a file exists with the same name
            $existingFile = File::where('folder_id', $this->folder_id)
                ->where('name', $this->name)->first();

            if (!$existingFile) {
                $this->save();
            } else {
                $this->name = $name . ' (' . ++$i . ')';

                // Do not exceed 1000 copies / failsafe to stop impending doom
                if ($i > 1000) {
                    return false;
                }
            }
        }

        // Update owner's used storage
        $this->owned_by->used_drive_bytes = $newUserDriveBytes;
        $this->owned_by->save();

        // Update the parent folders' storagee
        $this->folder->size += $this->size;
        $this->folder->save();

        $that = $this;

        $this->folder->recursiveForEachParent(function ($folder) use ($that) {
            $folder->size += $that->size;
            $folder->save();
        });

        return true;
    }

    /**
     * Delete the folder from storage and database.
     */
    public function permanentDelete()
    {
        // Update file owner's used drive bytes
        $this->owned_by->used_drive_bytes -= $this->size;
        $this->owned_by->save();

        // Update parent folders sizes
        $this->folder->size -= $this->size;
        $this->folder->save();

        $size = $this->size;

        $this->folder->recursiveForEachParent(function ($folder) use ($size) {
            $folder->size -= $size;
            $folder->save();
        });

        // Delete the file from storage
        Storage::disk('private')->delete($this->storage_path . '/' . $this->storage_basename);

        $this->forceDelete();
    }
}
