<?php

namespace Mydnic\Subscribers\Actions;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mydnic\Subscribers\Mail\CampaignMail;
use Mydnic\Subscribers\Models\Campaign;
use Mydnic\Subscribers\Models\CampaignSend;

class SendTestCampaignAction
{
    public function execute(Campaign $campaign, string $recipientEmail): void
    {
        // Build an in-memory send record (not persisted) so CampaignMail renders normally.
        // Tracking URLs will be generated but the token won't exist in the DB,
        // so opens/clicks simply won't be recorded — which is correct for a test.
        $send = new CampaignSend(['token' => Str::random(64)]);

        Mail::to($recipientEmail)->send(new CampaignMail($campaign, $send));
    }
}
