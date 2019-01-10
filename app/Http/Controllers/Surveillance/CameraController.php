<?php

namespace App\Http\Controllers\Surveillance;

use App\Events\Surveillance\MotionDetected;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest']);
    }

    /**
     * Stream the camera feed.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function streamFeed(Request $request, $camera)
    {
        // Set credentials for Basic auth
        $username = config('surveillance.auth.username');
        $password = config('surveillance.auth.password');
        $credentials = base64_encode($username.':'.$password);

        // Set headers
        $headers = 'Authorization: Basic '.$credentials."\r\n";
        $headers .= "Cache-Control: no-cache\r\n";
        $headers .= "Connection: keep-alive\r\n";

        // Create context
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headers,
            ]
        ]);

        // Set response headers
        header('Content-Type: multipart/x-mixed-replace; boundary=BoundaryString');
        header('Cache-Control: no-cache, private');
        header('Expires: 0');
        header('Max-Age: 0');
        header('Pragma: no-cache');

        // Stream feed
        readfile(config('surveillance.cameras.'.str_replace('-', '_', $camera)), false, $context);
    }

    /**
     * Webhook posted to when motion is detected.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function motionWebhook(Request $request)
    {
        event(new MotionDetected);
    }
}
