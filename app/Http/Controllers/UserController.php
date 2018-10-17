<?php

namespace App\Http\Controllers;

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
        //
    }

    /**
     * Return the curren user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrent(Request $request)
    {
        return $request->user();
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
            abort(403, 'Unauthorized.');
        }

        if ($user) {
            return $user;
        }

        $limit = $this->validatePaging($request);

        return User::paginate($limit);
    }

    /**
     * Store a user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->user()->cannot('create_users')) {
            abort(403, 'Unauthorized.');
        }

        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'verified' => 'required|boolean',
        ]);

        $user = new User;

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->slug = str_slug(explode('@', $request->input('email'))[0], '-');
        $user->password = bcrypt($request->input('password'));
        $user->email_verified_at = $request->input('verified') ? Carbon::now() : null;

        $user->save();

        $user->assignRole($request->input('role'));

        return $user;
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
        if ($request->user()->isNot($user) && $request->user()->cannot('update_users')) {
            abort(403, 'Unauthorized.');
        }

        $this->validate($request, [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->all();

        if ($request->has('password')) {
            $data['password'] = bcrypt($data['password']);
        }

        if ($request->has('email')) {
            $user->slug = str_slug(explode('@', $data['email'])[0], '-');
        }

        $user->fill($data)->save();

        return $user;
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
            abort(403, 'Unauthorized.');
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
            abort(403, 'Unauthorized.');
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
            abort(403, 'Unauthorized.');
        }

        $trashedUser->restore();

        return $trashedUser;
    }
}
