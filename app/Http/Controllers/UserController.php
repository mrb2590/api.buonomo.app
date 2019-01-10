<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use App\Models\User;
use App\Traits\HasPaging;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HasPaging;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:api', 'verified']);
    }

    /**
     * Return the current user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrent(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Return all or a single user.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request, User $user = null)
    {
        if ($request->user()->cannot('fetch_users')) {
            abort(403, 'You are not authorized to fetch users.');
        }

        if ($user) {
            return new UserResource($user);
        }

        $limit = $this->validatePaging($request);

        return UserResource::collection(User::paginate($limit));
    }

    /**
     * Create a user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'verified' => 'required|boolean',
            'allocated_drive_bytes' => 'nullable|integer|min:0',
        ]);

        if ($request->user()->cannot('create_users')) {
            abort(403, 'You are not authorized to create users.');
        }

        $user = new User;

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->slug = str_slug(explode('@', $request->input('email'))[0], '-');
        $user->password = bcrypt($request->input('password'));
        $user->email_verified_at = $request->input('verified') ? Carbon::now() : null;
        $user->allocated_drive_bytes = $request->input('allocated_drive_bytes');

        $user->save();

        // Create user's root folder
        $user->createRootFolder();

        return new UserResource($user);
    }

    /**
     * Update a user.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users',
            'password' => 'nullable|string|min:6',
            'verified' => 'required|boolean',
            'allocated_drive_bytes' => 'nullable|integer|min:0',
        ]);

        if ($request->user()->isNot($user) && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update other users.');
        }

        // Make sure current user set other's
        if ($request->has('verified') && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update your own verification.');
        }

        // Make sure current user can update their own drive storage
        if ($request->has('allocated_drive_bytes') && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update your drive storage.');
        }

        $data = $request->all();

        if ($request->has('password')) {
            $data['password'] = bcrypt($data['password']);
        }

        if ($request->has('email')) {
            $user->slug = str_slug(explode('@', $data['email'])[0], '-');
        }

        if ($request->has('verified')) {
            $user->email_verified_at = $request->input('verified') ? Carbon::now() : null;
        }

        $user->fill($data)->save();

        return new UserResource($user);
    }

    /**
     * Trash a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function trash(Request $request, User $user)
    {
        if ($request->user()->cannot('trash_users')) {
            abort(403, 'You are not authorized to trash users.');
        }

        $user->delete();

        return response('', 204);
    }

    /**
     * Delete a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User $trashedUser
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, User $trashedUser)
    {
        if ($request->user()->cannot('delete_users')) {
            abort(403, 'You are not authorized to delete users.');
        }

        $trashedUser->forceDelete();

        return response('', 204);
    }

    /**
     * Restore a trashed user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User $trashedUser
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, User $trashedUser)
    {
        if ($request->user()->cannot('restore_users')) {
            abort(403, 'You are not authorized to restore users.');
        }

        $trashedUser->restore();

        return new UserResource($trashedUser);
    }
}
