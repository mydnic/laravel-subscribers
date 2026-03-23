<?php

namespace Mydnic\Subscribers\Test;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mydnic\Subscribers\Events\EmailLinkClicked;
use Mydnic\Subscribers\Events\EmailOpened;
use Mydnic\Subscribers\Models\Campaign;
use Mydnic\Subscribers\Models\CampaignSend;
use Mydnic\Subscribers\Models\Subscriber;
use PHPUnit\Framework\Attributes\Test;

class TrackingTest extends TestCase
{
    private function makeSend(): CampaignSend
    {
        $subscriber = Subscriber::create(['email' => 'track@example.com']);

        $campaign = Campaign::create([
            'name' => 'Track Test',
            'subject' => 'Track',
            'status' => 'sending',
        ]);

        return CampaignSend::create([
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'token' => Str::random(64),
        ]);
    }

    #[Test]
    public function it_returns_tracking_pixel_gif(): void
    {
        $send = $this->makeSend();

        $response = $this->get("/subscribers/tracking/open/{$send->token}");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/gif');
    }

    #[Test]
    public function it_records_open_on_pixel_hit(): void
    {
        Event::fake([EmailOpened::class]);

        $send = $this->makeSend();

        $this->get("/subscribers/tracking/open/{$send->token}");

        $send->refresh();
        $this->assertEquals(1, $send->open_count);
        $this->assertNotNull($send->opened_at);

        Event::assertDispatched(EmailOpened::class);
    }

    #[Test]
    public function it_increments_open_count_on_multiple_hits(): void
    {
        $send = $this->makeSend();

        $this->get("/subscribers/tracking/open/{$send->token}");
        $this->get("/subscribers/tracking/open/{$send->token}");
        $this->get("/subscribers/tracking/open/{$send->token}");

        $send->refresh();
        $this->assertEquals(3, $send->open_count);
    }

    #[Test]
    public function it_redirects_on_click_tracking(): void
    {
        Event::fake([EmailLinkClicked::class]);

        $send = $this->makeSend();
        $url = base64_encode('https://example.com/page');

        $response = $this->get("/subscribers/tracking/click/{$send->token}?url={$url}");

        $response->assertRedirect('https://example.com/page');

        $send->refresh();
        $this->assertNotNull($send->clicked_at);
        $this->assertCount(1, $send->click_log);

        Event::assertDispatched(EmailLinkClicked::class);
    }

    #[Test]
    public function it_returns_200_for_unknown_token_on_open(): void
    {
        $response = $this->get('/subscribers/tracking/open/unknown-token-xyz');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/gif');
    }

    #[Test]
    public function it_rejects_invalid_base64_on_click(): void
    {
        $send = $this->makeSend();

        $response = $this->get("/subscribers/tracking/click/{$send->token}?url=not-valid-base64!!!");

        $response->assertStatus(400);
    }
}
