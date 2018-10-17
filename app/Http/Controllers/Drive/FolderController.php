<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
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
     * Return the current user's folder.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrent(Request $request)
    {
        return $request->user()->folder;
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
        if ($request->user()->cannot('fetch_folders')) {
            abort(403, 'Unauthorized.');
        }

        if ($folder) {
            return $folder;
        }

        $limit = $this->validatePaging($request);

        return Folder::paginate($limit);
    }

    /**
     * Create a folder.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->user()->id !== $request->input('owned_by_id') &&
            $request->user()->cannot('create_folders')
        ) {
            abort(403, 'Unauthorized.');
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'folder_id' => 'required|integer|exists:folders,id',
            'owned_by_id' => 'required|integer|exists:users,id',
        ]);

        $folder = new Folder;
        $folder->name = $request->input('name');
        $folder->folder_id = $request->input('folder_id');
        $folder->owned_by_id = $request->input('owned_by_id');
        $folder->created_by_id = $request->user()->id;

        $folder->save();

        return $folder;
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
        if ($request->user()->id !== $folder->owned_by_id &&
            $request->user()->cannot('update_folders')
        ) {
            abort(403, 'Unauthorized.');
        }

        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'folder_id' => 'nullable|integer|exists:folders,id',
            'owned_by_id' => 'nullable|integer|exists:users,id',
        ]);

        $folder->fill($request->all())->save();

        $folder->load('owned_by');

        return $folder;
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
        if ($request->user()->cannot('trash_folders')) {
            abort(403, 'Unauthorized.');
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
        if ($request->user()->cannot('delete_folders')) {
            abort(403, 'Unauthorized.');
        }

        $trashedFolder->forceDelete();

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
        if ($request->user()->cannot('restore_folders')) {
            abort(403, 'Unauthorized.');
        }

        $trashedFolder->restore();

        return $trashedFolder;
    }
}
