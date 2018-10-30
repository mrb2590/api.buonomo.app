<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Models\Drive\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    /**
     * Return the server data.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request)
    {
        return Server::data();
    }
}
