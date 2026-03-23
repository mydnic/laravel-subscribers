<?php

namespace Mydnic\Subscribers\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mydnic\Subscribers\Models\Subscriber;

class SubscriberDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Subscriber $subscriber,
    ) {}
}
