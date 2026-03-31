<?php

namespace Mydnic\Kanpen\Commands;

use Illuminate\Console\Command;
use Mydnic\Kanpen\Actions\SendCampaignAction;
use Mydnic\Kanpen\Enums\CampaignStatus;
use Mydnic\Kanpen\Models\Campaign;

class DispatchScheduledCampaignsCommand extends Command
{
    protected $signature = 'kanpen:dispatch-scheduled';

    protected $description = 'Dispatch all campaigns whose scheduled_at time has passed';

    public function handle(SendCampaignAction $action): int
    {
        $due = Campaign::where('status', CampaignStatus::Draft)
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled campaigns are due.');

            return self::SUCCESS;
        }

        $dispatched = 0;

        foreach ($due as $campaign) {
            $action->execute($campaign);
            $this->info("Dispatched: [{$campaign->id}] {$campaign->name}");
            $dispatched++;
        }

        $this->info("Done. {$dispatched} / {$due->count()} campaign(s) dispatched.");

        return self::SUCCESS;
    }
}
