<?php

namespace Mydnic\Subscribers\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Mydnic\Subscribers\Mail\CampaignMail;
use Mydnic\Subscribers\Models\CampaignSend;

class SendCampaignToSubscriberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly CampaignSend $send,
    ) {}

    public function handle(): void
    {
        $send = $this->send->load(['campaign', 'subscriber']);

        Mail::to($send->subscriber->email)
            ->send(new CampaignMail($send->campaign, $send));

        $send->update(['sent_at' => now()]);
    }
}
