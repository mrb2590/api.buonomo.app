<?php

namespace App\Http\Controllers;

use App\Http\Resources\Role as RoleResource;
use App\Models\User;
use App\Traits\HasPaging;
use Illuminate\Http\Request;

class UserRoleController extends Controller
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
     * Return the current user's roles.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrent(Request $request)
    {
        $limit = $this->validatePaging($request);

        return RoleResource::collection($request->user()->roles()->paginate($limit));
    }

    /**
     * Return a single user's roles.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request, User $user)
    {
        if ($request->user()->cannot('fetch_user_roles')) {
            abort(403, 'Unauthorized.');
        }

        $limit = $this->validatePaging($request);

        return RoleResource::collection($user->roles()->paginate($limit));
    }

    /**
     * Assign a user a role.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function assign(Request $request, User $user)
    {
        $this->validate($request, ['role' => 'required|string|exists:roles,name']);

        if ($request->user()->cannot('assign_user_roles')) {
            abort(403, 'Unauthorized.');
        }

        $user->assignRole($request->input('role'));

        return response('', 204);
    }

    /**
     * Remove a user's role.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request, User $user)
    {
        $this->validate($request, ['role' => 'required|string|exists:roles,name']);

        if ($request->user()->cannot('remove_user_roles')) {
            abort(403, 'Unauthorized.');
        }

        if ($user->doesNotHaveRole($request->input('role'))) {
            abort(409, 'User does not have the specified role.');
        }

        $user->removeRole($request->input('role'));

        return response('', 204);
    }
}
