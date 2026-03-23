<?php

namespace Mydnic\Subscribers\Actions;

use InvalidArgumentException;
use Mydnic\Subscribers\Jobs\SendCampaignJob;
use Mydnic\Subscribers\Models\Campaign;

class SendCampaignAction
{
    public function execute(Campaign $campaign): void
    {
        if (! $campaign->isDraft()) {
            throw new InvalidArgumentException(
                "Campaign [{$campaign->id}] cannot be sent because its status is [{$campaign->status->value}]."
            );
        }

        $queue = config('laravel-subscribers.campaigns.queue', 'default');

        SendCampaignJob::dispatch($campaign)->onQueue($queue);
    }
}
