<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Models\Drive\File;
use App\Models\Drive\Folder;
use App\Traits\HasPaging;
use Illuminate\Http\Request;

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

            $query = File::where('owned_by_id', $request->input('owned_by_id'))->paginate($limit);

            if ($limit) {
                return $query->paginate($limit);
            }
            
            return $query->get();
        }

        if ($request->user()->cannot('fetch_files')) {
            abort(403, 'You\'re not authorized to fetch files you don\'t own.');
        }

        return $limit ? File::paginate($limit) : File::all();
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

        $newUserDriveBytes = $parentFolder->owned_by->used_drive_bytes +
            $request->file('file')->getClientSize();

        // Make sure owner has ednough allocated storage
        if ($newUserDriveBytes > $parentFolder->owned_by->allocated_drive_bytes) {
            abort(403, 'The new owner does not have enough drive storage.');
        }

        $file = new File;
        $saved = $file->saveToStorage($request->file('file'), $parentFolder, $request->user());

        if (!$saved) {
            abort(500, 'Could not save the file to the server.');
        }

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

        $trashedFile->permanentDelete();

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
