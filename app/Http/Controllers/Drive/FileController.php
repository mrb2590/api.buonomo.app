<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Models\Drive\File;
use App\Models\Drive\Folder;
use App\Traits\HasPaging;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    use HasPaging;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Return all or a single file.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\File $file
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request, File $file = null)
    {
        if ($file) {
            if ($request->user()->id !== $file->owned_by_id &&
                $request->user()->cannot('fetch_files')
            ) {
                abort(403, 'You\'re not authorized to fetch files you don\'t own.');
            }

            return $file;
        }

        $this->validate($request, ['owned_by_id' => 'nullable|integer|exists:users,id']);

        $limit = $this->validatePaging($request);

        if ($request->has('owned_by_id')) {
            if ($request->user()->id !== (int) $request->input('owned_by_id')&&
                $request->user()->cannot('fetch_files')
            ) {
                abort(403, 'You\'re not authorized to fetch files you don\'t own.');
            }

            return File::where('owned_by_id', $request->input('owned_by_id'))->paginate($limit);
        }

        if ($request->user()->cannot('fetch_files')) {
            abort(403, 'You\'re not authorized to fetch files you don\'t own.');
        }

        return File::paginate($limit);
    }

    /**
     * Upload a file.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|max:20000000',
            'folder_id' => 'nullable|integer|exists:drive_folders,id',
        ]);

        $parentFolder = Folder::find($request->input('folder_id'));

        if ($request->user()->id !== $parentFolder->owned_by_id &&
            $request->user()->cannot('upload_files')
        ) {
            abort(403, 'You are not authorized to upload files in other user\'s folders.');
        }

        // Need to create the filename ourselves because Laravel will not always use the correct
        // extension
        $filename = Str::random(40).'.'.$request->file('file')->getClientOriginalExtension();
        $date = Carbon::now();
        $filePath = $date->format('Y').'/'.$date->format('m').'/'.$date->format('d');
        $path = Storage::disk('private')->putFileAs($filePath, $request->file('file'), $filename);
        $originalFilename = pathinfo($request->file('file')->getClientOriginalName())['filename'];
        $pathInfo = pathinfo($path);

        $file = new File;
        $file->filename = $pathInfo['filename'];
        $file->extension = strtolower($pathInfo['extension']);
        $file->storage_basename = $pathInfo['basename'];
        $file->storage_path = '/'.$filePath;
        $file->mime_type = $request->file('file')->getMimeType();
        $file->size = Storage::disk('private')->size($path);
        $file->folder_id = $request->input('folder_id');
        $file->owned_by_id = $request->user()->id;
        $file->created_by_id = $request->user()->id;
        $file->updated_by_id = $request->user()->id;
        $file->name = $originalFilename;

        $newUserDriveBytes = $parentFolder->owned_by->used_drive_bytes + $file->size;

        if ($newUserDriveBytes > $parentFolder->owned_by->allocated_drive_bytes) {
            Storage::disk('private')->delete($path);
            abort(403, 'The new owner does not have enough drive storage.');
        }

        $i = 0;
        $existingFile = true;

        while ($existingFile) {
            // Check if a file exists with the same name
            $existingFile = File::where('folder_id', $file->folder_id)
                ->where('name', $file->name)->first();
            
            if (!$existingFile) {
                $file->save();
            } else {
                $file->name = $originalFilename.' ('.++$i.')';
            }
        }

        // Update owner's used storage
        $file->owned_by->used_drive_bytes = $newUserDriveBytes;
        $file->owned_by->save();

        // Update the parent folders' storagee
        $file->folder->size += $file->size;
        $file->folder->save();

        $file->folder->recursiveForEachParent(function($folder) use ($file) {
            $folder->size += $file->size;
            $folder->save();
        });

        return $file;
    }

    /**
     * Update a file.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\File $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        $this->validate($request, ['name' => 'nullable|string|max:255']);

        if ($request->user()->id !== $file->owned_by_id &&
            $request->user()->cannot('update_files')
        ) {
            abort(403, 'You\'re not authorized to update files you don\'t own.');
        }

        $file->fill($request->all())->save();
        $file->updated_by_id = $request->user()->id;

        return $file;
    }

    /**
     * Move a file.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\File $file
     * @return \Illuminate\Http\Response
     */
    public function move(Request $request, File $file)
    {
        $this->validate($request, ['folder_id' => 'required|integer|exists:drive_folders,id']);

        $newParentFolder = Folder::find($request->input('folder_id'));

        if ($request->user()->id !== $file->owned_by_id &&
            $request->user()->cannot('move_files')
        ) {
            abort(403, 'You\'re not authorized to move files you don\'t own.');
        }

        if ($request->user()->id !== $newParentFolder->owned_by_id &&
            $request->user()->cannot('move_files')
        ) {
            abort(403, 'You\'re not authorized to move files to folders you don\'t own.');
        }

        if ($newParentFolder->owned_by_id !== $file->owned_by_id) {
            $newUserDriveBytes = $newParentFolder->owned_by->used_drive_bytes + $file->size;

            if ($newUserDriveBytes > $newParentFolder->owned_by->allocated_drive_bytes) {
                abort(403, 'The new owner does not have enough drive storage.');
            }
        }

        $file->moveTo($newParentFolder);

        return response('', 204);
    }

    /**
     * Download a file.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\File $file
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request, File $file)
    {
        if ($request->user()->id !== $file->owned_by_id &&
            $request->user()->cannot('download_files')
        ) {
            abort(403, 'You\'re not authorized to download files you don\'t own.');
        }

        return response()->download(
            storage_path('app/private'.$file->storage_path.'/'.$file->storage_basename),
            $file->name.'.'.$file->extension
        );
    }

    /**
     * Trash a file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Drive\File $file
     * @return \Illuminate\Http\Response
     */
    public function trash(Request $request, File $file)
    {
        if ($request->user()->id !== $file->owned_by_id &&
            $request->user()->cannot('trash_files')
        ) {
            abort(403, 'You are not authorized to trash other user\'s files.');
        }

        $file->delete();

        return response('', 204);
    }

    /**
     * Delete a file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Drive\File $trashedFile
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, File $trashedFile)
    {
        if ($request->user()->id !== $trashedFile->owned_by_id &&
            $request->user()->cannot('delete_files')
        ) {
            abort(403, 'Your\'re not authorized to delete files you don\'t own.');
        }

        // Update file owner's used drive bytes
        $trashedFile->owned_by->used_drive_bytes -= $trashedFile->size;
        $trashedFile->owned_by->save();

        $trashedFile->forceDelete();

        return response('', 204);
    }

    /**
     * Restore a trashed file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Drive\File $trashedFile
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, File $trashedFile)
    {
        if ($request->user()->id !== $trashedFile->owned_by_id &&
            $request->user()->cannot('restore_files')
        ) {
            abort(403, 'You\'re not authorized to restore files you don\'t own.');
        }

        $trashedFile->restore();

        return $trashedFile;
    }
}
