<?php

namespace Mydnic\Kanpen\Actions;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mydnic\Kanpen\Mail\CampaignMail;
use Mydnic\Kanpen\Models\Campaign;
use Mydnic\Kanpen\Models\CampaignDelivery;

class SendTestCampaignAction
{
    public function execute(Campaign $campaign, string $recipientEmail): void
    {
        // Build an in-memory send record (not persisted) so CampaignMail renders normally.
        // Tracking URLs will be generated but the token won't exist in the DB,
        // so opens/clicks simply won't be recorded — which is correct for a test.
        $send = new CampaignDelivery(['token' => Str::random(64)]);

        Mail::to($recipientEmail)->send(new CampaignMail($campaign, $send));
    }
}
