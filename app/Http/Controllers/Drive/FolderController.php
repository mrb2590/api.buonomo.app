<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Http\Resources\Drive\Folder as FolderResource;
use App\Models\Drive\Folder;
use App\Traits\HasPaging;
use Illuminate\Http\Request;

class FolderController extends Controller
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
     * Return the current user's root folder.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrent(Request $request)
    {
        return new FolderResource($request->user()->folder);
    }

    /**
     * Return all or a single folder.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request, Folder $folder = null)
    {
        if ($folder) {
            if ($request->user()->id !== $folder->owned_by_id &&
                $request->user()->cannot('fetch_folders')
            ) {
                abort(403, 'You\'re not authorized to fetch folders you don\'t own.');
            }

            return new FolderResource($folder);
        }

        $this->validate($request, ['owned_by_id' => 'nullable|uuid|exists:users,id']);

        $limit = $this->validatePaging($request);

        if ($request->has('owned_by_id')) {
            if ($request->user()->id !== (int) $request->input('owned_by_id') &&
                $request->user()->cannot('fetch_folders')
            ) {
                abort(403, 'You\'re not authorized to fetch folders you don\'t own.');
            }

            $query = Folder::where('owned_by_id', $request->input('owned_by_id'));

            return FolderResource::collection($query->paginate($limit));
        }

        if ($request->user()->cannot('fetch_folders')) {
            abort(403, 'You\'re not authorized to fetch folders you don\'t own.');
        }

        return FolderResource::collection(Folder::paginate($limit));
    }

    /**
     * Return all child folders of a folder
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function fetchFolders(Request $request, Folder $folder)
    {
        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('fetch_folders')
        ) {
            abort(403, 'You\'re not authorized to fetch folders from folders you don\'t own.');
        }

        $limit = $this->validatePaging($request);

        return FolderResource::collection($folder->folders()->paginate($limit));
    }

    /**
     * Return all child files of a folder
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function fetchFiles(Request $request, Folder $folder)
    {
        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('fetch_files')
        ) {
            abort(403, 'You\'re not authorized to fetch files from folders you don\'t own.');
        }

        $limit = $this->validatePaging($request);

        return FolderResource::collection($folder->files()->paginate($limit));
    }

    /**
     * Return a single folder with it's direct child folders and files.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function fetchList(Request $request, Folder $folder)
    {
        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('fetch_folders')
        ) {
            abort(403, 'You\'re not authorized to fetch folders you don\'t own.');
        }

        $folder->load('folders', 'files');

        return new FolderResource($folder);
    }

    /**
     * Create a folder.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|regex:/^(?!\.+)[\w,\s-\.]+[\w,\s-]$/',
            'folder_id' => 'required|uuid|exists:drive_folders,id',
        ]);

        $parentFolder = Folder::find($request->input('folder_id'));

        if ($request->user()->id !== $parentFolder->owned_by_id &&
            $request->user()->cannot('create_folders')
        ) {
            abort(403, 'You are not authorized to create folders in other users\' folders.');
        }

        $folder = new Folder;
        $folder->name = $request->input('name');
        $folder->folder_id = $parentFolder->id;
        $folder->owned_by_id = $parentFolder->owned_by_id;
        $folder->created_by_id = $request->user()->id;
        $folder->updated_by_id = $request->user()->id;

        try {
            $folder->save();
        } catch (\Illuminate\Database\QueryException $e) {
            abort(409, 'A folder with the name "' . $folder->name . '" already exists.');
        }

        return new FolderResource($folder);
    }

    /**
     * Update a folder.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Folder $folder)
    {
        $this->validate($request, ['name' => 'nullable|string|max:255']);

        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('update_folders')
        ) {
            abort(403, 'You\'re not authorized to update folders you don\'t own.');
        }

        $folder->fill($request->all())->save();
        $folder->updated_by_id = $request->user()->id;

        return new FolderResource($folder);
    }

    /**
     * Move a folder.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function move(Request $request, Folder $folder)
    {
        $this->validate($request, ['folder_id' => 'required|uuid|exists:drive_folders,id']);

        $newParentFolder = Folder::find($request->input('folder_id'));

        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('move_folders')
        ) {
            abort(403, 'You\'re not authorized to move folders you don\'t own.');
        }

        if ($request->user()->id !== $newParentFolder->owned_by_id &&
            $request->user()->cannot('move_folders')
        ) {
            abort(403, 'You\'re not authorized to move folders to folders you don\'t own.');
        }

        if ($folder->id === (int) $request->input('folder_id')) {
            abort(409, 'You cannot move a folder into itself.');
        }

        if ($folder->folder_id === null) {
            $msg = 'The folder you\'re requesting to move is a root folder and can\'t be ';
            $msg .= 'moved.';

            abort(409, $msg);
        }

        if ($newParentFolder->owned_by_id !== $folder->owned_by_id) {
            $newUserDriveBytes = $newParentFolder->owned_by->used_drive_bytes + $folder->size;

            if ($newUserDriveBytes > $newParentFolder->owned_by->allocated_drive_bytes) {
                abort(403, 'The new owner does not have enough drive storage.');
            }
        }

        $folder->moveTo($newParentFolder);

        return response('', 204);
    }

    /**
     * Download folder as a zip.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request, Folder $folder)
    {
        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('download_folders')
        ) {
            abort(403, 'You\'re not authorized to download folders you don\'t own.');
        }

        $zipPath = $folder->packageZip();

        return response()->download($zipPath, $folder->name . '.zip')->deleteFileAfterSend(true);
    }

    /**
     * Trash a folder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Drive\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function trash(Request $request, Folder $folder)
    {
        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('trash_folders')
        ) {
            abort(403, 'You are not authorized to trash other user\'s folders.');
        }

        if ($folder->folder_id === null) {
            $msg = 'The folder you\'re requesting to trash is a root folder and can\'t be ';
            $msg .= 'trashed.';

            abort(409, $msg);
        }

        $folder->delete();

        return response('', 204);
    }

    /**
     * Delete a folder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Drive\Folder $trashedFolder
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, Folder $trashedFolder)
    {
        if ($request->user()->id !== $trashedFolder->owned_by_id &&
            $request->user()->cannot('delete_folders')
        ) {
            abort(403, 'Your\'re not authorized to delete folders you don\'t own.');
        }

        if ($trashedFolder->folder_id === null) {
            $msg = 'The folder you\'re requesting to delete is a root folder and can\'t be ';
            $msg .= 'deleted.';

            abort(409, $msg);
        }

        $trashedFolder->permanentDelete();

        return response('', 204);
    }

    /**
     * Restore a trashed folder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Drive\Folder $trashedFolder
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, Folder $trashedFolder)
    {
        if ($request->user()->id !== $trashedFolder->owned_by_id &&
            $request->user()->cannot('restore_folders')
        ) {
            abort(403, 'You\'re not authorized to restore folders you don\'t own.');
        }

        $trashedFolder->restore();

        return new FolderResource($trashedFolder);
    }
}
