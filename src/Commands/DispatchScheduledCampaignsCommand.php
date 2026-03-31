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
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled campaigns are due.');

            return self::SUCCESS;
        }

        $dispatched = 0;

        foreach ($due as $campaign) {
            try {
                $action->execute($campaign);
                $this->info("Dispatched: [{$campaign->id}] {$campaign->name}");
                $dispatched++;
            } catch (\Throwable $e) {
                $this->error("Failed [{$campaign->id}] {$campaign->name}: {$e->getMessage()}");
            }
        }

        $this->info("Done. {$dispatched} / {$due->count()} campaign(s) dispatched.");

        return self::SUCCESS;
    }
}
