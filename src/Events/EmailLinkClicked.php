<?php

namespace Mydnic\Subscribers\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mydnic\Subscribers\Models\CampaignSend;

class EmailLinkClicked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CampaignSend $send,
        public readonly string $url,
    ) {}
}
