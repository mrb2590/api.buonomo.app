<?php

namespace App\Listeners\Surveillance;

use App\Models\User;
use App\Notifications\Surveillance\MotionDetected;

class SendEmailMotionDetectedNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $user = User::where('email', 'mrb2590@gmail.com')->first();
        $user->notify(new MotionDetected);
    }
}
