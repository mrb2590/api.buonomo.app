<?php

namespace App\Http\Controllers\RaspberryPi;

use App\Http\Controllers\Controller;
use App\Jobs\RaspberryPi\RunProgram;
use Illuminate\Http\Request;

class LightController extends Controller
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
    public function runLightsProgramWebhook(Request $request)
    {
        $possibleIterations = [8, 16, 32, 64, 128];
        $possibleSpeeds = [50, 100, 250, 500, 1000];
        $mode = 'circle';
        $speed = '50';
        $iterations = '64';

        if (in_array($request->input('queryResult.parameters.iterations'), $possibleIterations)) {
            $iterations = $request->input('queryResult.parameters.iterations');
        }

        if (in_array($request->input('queryResult.parameters.speed'), $possibleSpeeds)) {
            $speed = $request->input('queryResult.parameters.speed');
        }

        $command = 'ssh -t mbuonomo@mikerpi3bplus ';
        $command .= '"sudo node /home/mbuonomo/Code/Lights/lights.js ';
        // $command .= 'mode=circle speed=50 iterations=64"';
        $command .= 'mode='.$mode;
        $command .= ' speed='.$speed;
        $command .= ' iterations='.$iterations.'"';

        RunProgram::dispatch($command);

        return [
            "fulfillmentText" => "The program will start shortly. Ya like that?",
        ];
    }
}
