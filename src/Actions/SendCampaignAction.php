<?php

namespace Mydnic\Kanpen\Actions;

use InvalidArgumentException;
use Mydnic\Kanpen\Jobs\SendCampaignJob;
use Mydnic\Kanpen\Models\Campaign;

class SendCampaignAction
{
    public function execute(Campaign $campaign): void
    {
        if (! $campaign->isDraft()) {
            throw new InvalidArgumentException(
                "Campaign [{$campaign->id}] cannot be sent because its status is [{$campaign->status->value}]."
            );
        }

        $queue = config('kanpen.campaigns.queue', 'default');

        SendCampaignJob::dispatch($campaign)->onQueue($queue);
    }
}
