<?php

namespace Mydnic\Subscribers\Traits;

use Mydnic\Subscribers\Models\Subscriber;

trait CanSubscribe
{
    public function subscribe(): void
    {
        $subscriber = Subscriber::withTrashed()
            ->where('email', $this->email)
            ->first();

        if ($subscriber && $subscriber->trashed()) {
            $subscriber->restore();
        } elseif (! $subscriber) {
            $subscriber = Subscriber::create(['email' => $this->email]);
        }

        if (config('laravel-subscribers.verify') && ! $subscriber->hasVerifiedEmail()) {
            $subscriber->sendEmailVerificationNotification();
        }
    }

    public function unsubscribe(): void
    {
        Subscriber::where('email', $this->email)->delete();
    }

    public function isSubscribed(): bool
    {
        return Subscriber::where('email', $this->email)->exists();
    }
}
