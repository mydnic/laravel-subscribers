<?php

namespace Mydnic\Kanpen\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Mydnic\Kanpen\Enums\CampaignStatus;
use Mydnic\Kanpen\Events\CampaignSent;
use Mydnic\Kanpen\Models\Campaign;
use Mydnic\Kanpen\Models\CampaignDelivery;
use Mydnic\Kanpen\Models\Subscriber;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Campaign $campaign,
    ) {}

    public function handle(): void
    {
        $this->campaign->update(['status' => CampaignStatus::Sending]);

        $query = Subscriber::query();

        if (config('kanpen.verify')) {
            $query->whereNotNull('email_verified_at');
        }

        $queue = config('kanpen.campaigns.queue', 'default');

        $query->chunk(100, function ($subscribers) use ($queue) {
            foreach ($subscribers as $subscriber) {
                $send = CampaignDelivery::create([
                    'campaign_id' => $this->campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'token' => Str::random(64),
                ]);

                SendCampaignToSubscriberJob::dispatch($send)->onQueue($queue);
            }
        });

        $this->campaign->update([
            'status' => CampaignStatus::Sent,
            'sent_at' => now(),
        ]);

        CampaignSent::dispatch($this->campaign);
    }
}
