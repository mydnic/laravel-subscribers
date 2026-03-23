<?php

namespace Mydnic\Subscribers\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mydnic\Subscribers\Models\Campaign;

class CampaignSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Campaign $campaign,
    ) {}
}
