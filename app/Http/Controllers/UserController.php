<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\HasPaging;
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
}
