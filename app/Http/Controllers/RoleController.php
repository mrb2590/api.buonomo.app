<?php

namespace App\Http\Controllers;

use App\Http\Resources\Role as RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
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
     * Return all roles.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request)
    {
        if ($request->user()->cannot('fetch_user_roles')) {
            abort(403, 'Unauthorized.');
        }

        return RoleResource::collection(Role::all());
    }
}
