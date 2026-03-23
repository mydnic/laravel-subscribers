<?php

namespace Mydnic\Subscribers\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mydnic\Subscribers\Models\CampaignSend;

class EmailOpened
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CampaignSend $send,
    ) {}
}
