<?php

namespace App\Http\Controllers;

use App\Http\Resources\Avatar as AvatarResource;
use App\Models\Avatar;
use App\Models\User;
use Illuminate\Http\Request;

class AvatarController extends Controller
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
     * Return the current user's avatar.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrent(Request $request)
    {
        return new AvatarResource($request->user()->avatar);
    }

    /**
     * Return the avatar options.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchOptions(Request $request)
    {
        return [
            'avatarStyles' => Avatar::$avatarStyles,
            'accessoriesTypes' => Avatar::$accessoriesTypes,
            'clotheTypes' => Avatar::$clotheTypes,
            'clotheColors' => Avatar::$clotheColors,
            'graphicTypes' => Avatar::$graphicTypes,
            'eyebrowTypes' => Avatar::$eyebrowTypes,
            'eyeTypes' => Avatar::$eyeTypes,
            'facialHairTypes' => Avatar::$facialHairTypes,
            'facialHairColors' => Avatar::$facialHairColors,
            'hairColors' => Avatar::$hairColors,
            'mouthTypes' => Avatar::$mouthTypes,
            'skinColors' => Avatar::$skinColors,
            'topTypes' => Avatar::$topTypes,
        ];
    }

    /**
     * Create an avatar.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'avatar_style' => 'required|string|in:'.implode(',', Avatar::$avatarStyles),
            'accessories_type' => 'required|string|in:'.implode(',', Avatar::$accessoriesTypes),
            'clothe_type' => 'required|string|in:'.implode(',', Avatar::$clotheTypes),
            'clothe_color' => 'required|string|in:'.implode(',', Avatar::$clotheColors),
            'graphic_type' => 'required|string|in:'.implode(',', Avatar::$graphicTypes),
            'eyebrow_type' => 'required|string|in:'.implode(',', Avatar::$eyebrowTypes),
            'eye_type' => 'required|string|in:'.implode(',', Avatar::$eyeTypes),
            'facial_hair_type' => 'required|string|in:'.implode(',', Avatar::$facialHairTypes),
            'facial_hair_color' => 'required|string|in:'.implode(',', Avatar::$facialHairColors),
            'hair_color' => 'required|string|in:'.implode(',', Avatar::$hairColors),
            'mouth_type' => 'required|string|in:'.implode(',', Avatar::$mouthTypes),
            'skin_color' => 'required|string|in:'.implode(',', Avatar::$skinColors),
            'top_type' => 'required|string|in:'.implode(',', Avatar::$topTypes),
        ]);

        $avatar = new Avatar;

        $avatar->user_id = $request->user()->id;
        $avatar->avatar_style = $request->input('avatar_style');
        $avatar->accessories_type = $request->input('accessories_type');
        $avatar->clothe_type = $request->input('clothe_type');
        $avatar->clothe_color = $request->input('clothe_color');
        $avatar->graphic_type = $request->input('graphic_type');
        $avatar->eyebrow_type = $request->input('eyebrow_type');
        $avatar->eye_type = $request->input('eye_type');
        $avatar->facial_hair_type = $request->input('facial_hair_type');
        $avatar->facial_hair_color = $request->input('facial_hair_color');
        $avatar->hair_color = $request->input('hair_color');
        $avatar->mouth_type = $request->input('mouth_type');
        $avatar->skin_color = $request->input('skin_color');
        $avatar->top_type = $request->input('top_type');

        $avatar->save();

        return new AvatarResource($avatar);
    }

    /**
     * Update an avatar.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'avatar_style' => 'required|string|in:'.implode(',', Avatar::$avatarStyles),
            'accessories_type' => 'required|string|in:'.implode(',', Avatar::$accessoriesTypes),
            'clothe_type' => 'required|string|in:'.implode(',', Avatar::$clotheTypes),
            'clothe_color' => 'required|string|in:'.implode(',', Avatar::$clotheColors),
            'graphic_type' => 'required|string|in:'.implode(',', Avatar::$graphicTypes),
            'eyebrow_type' => 'required|string|in:'.implode(',', Avatar::$eyebrowTypes),
            'eye_type' => 'required|string|in:'.implode(',', Avatar::$eyeTypes),
            'facial_hair_type' => 'required|string|in:'.implode(',', Avatar::$facialHairTypes),
            'facial_hair_color' => 'required|string|in:'.implode(',', Avatar::$facialHairColors),
            'hair_color' => 'required|string|in:'.implode(',', Avatar::$hairColors),
            'mouth_type' => 'required|string|in:'.implode(',', Avatar::$mouthTypes),
            'skin_color' => 'required|string|in:'.implode(',', Avatar::$skinColors),
            'top_type' => 'required|string|in:'.implode(',', Avatar::$topTypes),
        ]);

        if ($request->user()->isNot($user) && $request->user()->cannot('update_users')) {
            abort(403, 'You are not authorized to update other users\'s avatars.');
        }

        $user->avatar->user_id = $request->user()->id;
        $user->avatar->avatar_style = $request->input('avatar_style');
        $user->avatar->accessories_type = $request->input('accessories_type');
        $user->avatar->clothe_type = $request->input('clothe_type');
        $user->avatar->clothe_color = $request->input('clothe_color');
        $user->avatar->graphic_type = $request->input('graphic_type');
        $user->avatar->eyebrow_type = $request->input('eyebrow_type');
        $user->avatar->eye_type = $request->input('eye_type');
        $user->avatar->facial_hair_type = $request->input('facial_hair_type');
        $user->avatar->facial_hair_color = $request->input('facial_hair_color');
        $user->avatar->hair_color = $request->input('hair_color');
        $user->avatar->mouth_type = $request->input('mouth_type');
        $user->avatar->skin_color = $request->input('skin_color');
        $user->avatar->top_type = $request->input('top_type');

        $user->avatar->save();

        return new AvatarResource($user->avatar);
    }
}
