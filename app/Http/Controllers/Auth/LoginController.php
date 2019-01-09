<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Return an access token for a first-party Javascript app.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchToken(Request $request)
    {
        $this->validate($request, [
            'grant_type' => 'required|string|in:password,refresh_token',
            'email' => 'required_unless:grant_type,refresh_token|string|email|max:255',
            'password' => 'required_unless:grant_type,refresh_token|string',
            'refresh_token' => 'required_unless:grant_type,password|string',
        ]);

        $client = new \GuzzleHttp\Client(['base_uri' => config('app.url')]);

        $formParams = [
            'grant_type' => $request->input('grant_type'),
            'client_id' => config('passport.password_client.id'),
            'client_secret' => config('passport.password_client.secret'),
            'scope' => '*',
        ];

        if ($request->input('grant_type') == 'password') {
            $formParams['username'] = $request->input('email');
            $formParams['password'] = $request->input('password');
        } else {
            $formParams['refresh_token'] = $request->input('refresh_token');
        }

        try {
            $response = $client->request('POST', '/oauth/token', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $formParams,
            ]);

            return response()->json(json_decode($response->getBody()->getContents()));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                abort(401, 'The username or password is incorrect.');
            } else {
                abort($e->getResponse()->getStatusCode(), 'Failed to fetch token.');
            }
        }
    }
}
