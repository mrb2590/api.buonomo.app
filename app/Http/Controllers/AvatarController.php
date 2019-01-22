<?php

namespace App\Http\Controllers;

use App\Http\Resources\Avatar as AvatarResource;
use App\Models\Avatar;
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
            'styles' => Avatar::$styles,
            'accessories' => Avatar::$accessories,
            'clothesTypes' => Avatar::$clothesTypes,
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
            'style' => 'required|string|in:'.implode(',', Avatar::$styles),
            'accessories' => 'required|string|in:'.implode(',', Avatar::$accessories),
            'clothes_type' => 'required|string|in:'.implode(',', Avatar::$clothesTypes),
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
        $avatar->style = $request->input('style');
        $avatar->accessories = $request->input('accessories');
        $avatar->clothes_type = $request->input('clothes_type');
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
}
