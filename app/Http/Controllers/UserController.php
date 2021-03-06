<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use App\Models\User;
use App\Traits\HasPaging;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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
        $this->middleware(['auth:api', 'verified'])->except([
            'forgotPassword',
            'resetPassword'
        ]);
    }

    /**
     * Return the current user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrent(Request $request)
    {
        $this->validate($request, [
            'with_roles' => 'nullable|boolean',
            'with_avatar' => 'nullable|boolean',
        ]);

        $load = [];

        if ((bool) $request->input('with_roles')) {
            array_push($load, 'roles');
        }

        if ((bool) $request->input('with_avatar')) {
            array_push($load, 'avatar');
        }

        return new UserResource($request->user()->load($load));
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

        $this->validate($request, [
            'with_roles' => 'nullable|boolean',
            'with_avatar' => 'nullable|boolean',
        ]);

        $load = [];

        if ((bool) $request->input('with_roles')) {
            array_push($load, 'roles');
        }

        if ((bool) $request->input('with_avatar')) {
            array_push($load, 'avatar');
        }

        if ($user) {
            return new UserResource($user->load($load));
        }

        $searchableCols = ['first_name', 'last_name', 'email', 'username'];
        $sortableCols = array_merge($searchableCols, [
            'username',
            'verified_at',
            'created_at',
            'allocated_drive_bytes',
            'used_drive_bytes',
            'email_verified_at',
        ]);
        $limit = $this->validatePaging($request, User::class, $sortableCols);
        $query = User::query()->with($load);

        if ($request->has('search')) {
            for ($i = 0; $i < count($searchableCols); $i++) {
                if ($i === 0) {
                    $query->where($searchableCols[$i], 'like', '%'.$request->input('search').'%');
                } else {
                    $query->orWhere($searchableCols[$i], 'like', '%'.$request->input('search').'%');
                }
            }
        }

        if ($request->has('sort')) {
            $query->orderBy($request->input('sortby'), $request->input('sort'));
        }

        return UserResource::collection($query->paginate($limit));
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
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:30|regex:/^[a-zA-Z0-9._-]{0,30}$/|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'verified' => 'required|boolean',
            'allocated_drive_bytes' => 'nullable|integer|min:0',
            'roles' => 'nullable|array|exists:roles,name',
        ]);

        if ($request->user()->cannot('create_users')) {
            abort(403, 'You are not authorized to create users.');
        }

        $user = new User;
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->username = $request->input('username');
        $user->password = bcrypt($request->input('password'));
        $user->email_verified_at = $request->input('verified') ? Carbon::now() : null;
        $user->allocated_drive_bytes = $request->input('allocated_drive_bytes');
        $user->save();

        if (!$user->email_verified_at && !App::environment('production')) {
            $user->sendEmailVerificationNotification();
        }

        $user->createRootFolder();
        $user->createRandomAvatar();

        if ($request->has('roles')) {
            foreach ($request->input('roles') as $role) {
                $user->assignRole($role);
            }

            $user->load('roles');
        }

        return new UserResource($user);
    }

    /**
     * Update a user's profile.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request, User $user)
    {
        $unique = $user->username != $request->input('username') ? '|unique:users' : '';

        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:30|regex:/^[a-zA-Z0-9._-]{0,30}$/'.$unique,
            'verified' => 'nullable|boolean',
            'allocated_drive_bytes' => 'nullable|integer|min:0',
            'roles' => 'nullable|array|exists:roles,name',
        ]);

        if ($request->user()->isNot($user) && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update other user\'s profiles.');
        }

        // Make sure the user can update the verification
        if ($request->has('verified') && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update your own verification.');
        }

        // Make sure current user can update their own drive storage
        if ($request->has('allocated_drive_bytes') && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update your drive storage.');
        }

        // Make sure current user can update their own drive storage
        if ($request->has('roles') && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update your own roles.');
        }

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->username = $request->input('username');

        if ($request->has('verified')) {
            $user->email_verified_at = $request->input('verified') ? Carbon::now() : null;
        }

        if ($request->has('allocated_drive_bytes')) {
            $user->allocated_drive_bytes = $request->input('allocated_drive_bytes');
        }

        if ($request->has('roles')) {
            foreach ($user->roles as $role) {
                $user->removeRole($role->name);
            }

            foreach ($request->input('roles') as $role) {
                $user->assignRole($role);
            }

            $user->load('roles');
        }

        $user->save();

        return new UserResource($user);
    }
 
    /**
     * Update a user's email address.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function updateEmail(Request $request, User $user)
    {
        $unique = $user->email != $request->input('email') ? '|unique:users' : '';

        $this->validate($request, [
            'email' => 'required|string|email|max:255'.$unique,
        ]);

        if ($request->user()->isNot($user) && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update other users.');
        }

        $user->email = $request->input('email');
        $user->save();

        return new UserResource($user);
    }

    /**
     * Update a user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, User $user)
    {
        $this->validate($request, [
            'current_password' => 'nullable|string',
            'password' => 'required|string|confirmed|min:6',
            'password_confirmation' => 'required|string|min:6',
        ]);

        if ($request->user()->isNot($user) && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update other users.');
        }

        if ($request->user()->cannot('update_users') && !$request->has('current_password')) {
            abort(403, 'You must confirm your current password.');
        }

        if ($request->has('current_password')) {
            if (!Hash::check($request->input('current_password'), $request->user()->password)) {
                abort(401, 'Current password does not match.');
            }
        }

        $user->password = bcrypt($request->input('password'));
        $user->save();

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

    /**
     * Send reset password email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        Password::broker()->sendResetLink($request->only('email'));
    }

    /**
     * Reset a user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $token
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request, $token)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6'
        ]);

        $credentials = array_merge($request->only(
            'email', 'password', 'password_confirmation'
        ), ['token' => $token]);

        $response = Password::broker()->reset(
            $credentials, function($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));

                Auth::guard()->login($user);
            }
        );

        if ($response != Password::PASSWORD_RESET) {
            abort('401', 'The token is invalid for this email address.');
        }
    }
}
