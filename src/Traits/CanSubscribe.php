<?php

namespace Mydnic\Subscribers\Traits;

use Mydnic\Subscribers\Subscriber;

trait CanSubscribe
{
    public function subscribe()
    {
        $subscriber = Subscriber::create(['email' => $this->email]);

        if (config('laravel-subscribers.verify')) {
            $subscriber->sendEmailVerificationNotification();
        }
    }

    public function unsubscribe()
    {
        Subscriber::where('email', $this->email)->delete();
    }
}
