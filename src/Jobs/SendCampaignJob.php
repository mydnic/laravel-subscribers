<?php

namespace Mydnic\Subscribers\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Mydnic\Subscribers\Enums\CampaignStatus;
use Mydnic\Subscribers\Events\CampaignSending;
use Mydnic\Subscribers\Events\CampaignSent;
use Mydnic\Subscribers\Models\Campaign;
use Mydnic\Subscribers\Models\CampaignSend;
use Mydnic\Subscribers\Models\Subscriber;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Campaign $campaign,
    ) {}

    public function handle(): void
    {
        $this->campaign->update(['status' => CampaignStatus::Sending]);

        CampaignSending::dispatch($this->campaign);

        $query = Subscriber::query();

        if (config('laravel-subscribers.verify')) {
            $query->whereNotNull('email_verified_at');
        }

        $sentCount = 0;

        $queue = config('laravel-subscribers.campaigns.queue', 'default');

        $query->chunk(100, function ($subscribers) use (&$sentCount, $queue) {
            foreach ($subscribers as $subscriber) {
                $send = CampaignSend::create([
                    'campaign_id' => $this->campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'token' => Str::random(64),
                ]);

                SendCampaignToSubscriberJob::dispatch($send)->onQueue($queue);
                $sentCount++;
            }
        });

        $this->campaign->update([
            'status' => CampaignStatus::Sent,
            'sent_count' => $sentCount,
            'sent_at' => now(),
        ]);

        CampaignSent::dispatch($this->campaign);
    }
}
