<?php

namespace Mydnic\Kanpen\Test;

use Illuminate\Support\Facades\Bus;
use Mydnic\Kanpen\Enums\CampaignStatus;
use Mydnic\Kanpen\Jobs\SendCampaignJob;
use Mydnic\Kanpen\Models\Campaign;
use PHPUnit\Framework\Attributes\Test;

class ScheduledDispatchTest extends TestCase
{
    #[Test]
    public function it_dispatches_due_campaigns(): void
    {
        Bus::fake();

        Campaign::create([
            'name' => 'Due Campaign',
            'subject' => 'Subject',
            'status' => CampaignStatus::Draft->value,
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('kanpen:dispatch-scheduled')->assertSuccessful();

        Bus::assertDispatched(SendCampaignJob::class);
    }

    #[Test]
    public function it_skips_future_campaigns(): void
    {
        Bus::fake();

        Campaign::create([
            'name' => 'Future Campaign',
            'subject' => 'Subject',
            'status' => CampaignStatus::Draft->value,
            'scheduled_at' => now()->addHour(),
        ]);

        $this->artisan('kanpen:dispatch-scheduled')->assertSuccessful();

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_skips_non_draft_campaigns(): void
    {
        Bus::fake();

        Campaign::create([
            'name' => 'Already Sent',
            'subject' => 'Subject',
            'status' => CampaignStatus::Sent->value,
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('kanpen:dispatch-scheduled')->assertSuccessful();

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_skips_campaigns_without_scheduled_at(): void
    {
        Bus::fake();

        Campaign::create([
            'name' => 'Unscheduled',
            'subject' => 'Subject',
            'status' => CampaignStatus::Draft->value,
        ]);

        $this->artisan('kanpen:dispatch-scheduled')->assertSuccessful();

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_dispatches_only_due_campaigns_when_mixed(): void
    {
        Bus::fake();

        $due = Campaign::create([
            'name' => 'Due',
            'subject' => 'Subject',
            'status' => CampaignStatus::Draft->value,
            'scheduled_at' => now()->subMinutes(5),
        ]);

        Campaign::create([
            'name' => 'Future',
            'subject' => 'Subject',
            'status' => CampaignStatus::Draft->value,
            'scheduled_at' => now()->addHour(),
        ]);

        $this->artisan('kanpen:dispatch-scheduled')->assertSuccessful();

        Bus::assertDispatchedTimes(SendCampaignJob::class, 1);
        Bus::assertDispatched(SendCampaignJob::class, fn ($job) => $job->campaign->id === $due->id);
    }
}
