<?php

namespace Mydnic\Kanpen\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mydnic\Kanpen\Models\Subscriber;

class SubscriberCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Subscriber $subscriber,
    ) {}
}
