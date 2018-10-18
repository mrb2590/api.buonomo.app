<?php

namespace App\Models\Drive;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

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
        'created_by_id' => 'integer',
        'owned_by_id' => 'integer',
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
    protected $appends = ['path'];

    /**
     * The relationships to always load.
     *
     * @var array
     */
    protected $with = ['owned_by', 'created_by'];

    /**
     * Get the folder path.
     *
     * @return string   
     */
    protected function getPathAttribute()
    {
        $path = '';

        $this->traverseAllParentFolders(function($folder) use (&$path) {
            $path = $folder->name.'/'.$path;
        });

        $path = '/'.$path;

        return $path;
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
        return $this->belongsTo(User::class, 'owned_by_id')->publicInfo();
    }

    /**
     * Get the creator of the folder.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id')->publicInfo();
    }

    /**
     * Move this folder to another folder.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function moveTo(Folder $folder)
    {
        $this->traverseAllChildFolders(function($childFolder) use ($folder) {
            $childFolder->owned_by_id = $folder->owned_by_id;
            $childFolder->save();
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

        $this->traverseAllChildFolders(function($folder) use (&$zip) {
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
    private function traverseAllParentFolders(\Closure $callback)
    {
        $callback($this);

        if ($this->folder_id !== null) {
            $folder = Folder::find($this->folder_id);

            $folder->traverseAllParentFolders($callback);
        }
    }

    /**
     * Recursively traverse all child folders.
     *
     * @param  \Closure $callback
     */
    private function traverseAllChildFolders(\Closure $callback)
    {
        $callback($this);

        foreach ($this->folders as $folder) {
            $folder->traverseAllChildFolders($callback);
        }
    }
}
