<?php

namespace Mydnic\Kanpen\Test;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Mydnic\Kanpen\Actions\SendCampaignAction;
use Mydnic\Kanpen\Enums\CampaignStatus;
use Mydnic\Kanpen\Jobs\SendCampaignJob;
use Mydnic\Kanpen\Mail\CampaignMail;
use Mydnic\Kanpen\Models\Campaign;
use Mydnic\Kanpen\Models\CampaignDelivery;
use PHPUnit\Framework\Attributes\Test;

class CampaignTest extends TestCase
{
    #[Test]
    public function it_creates_a_campaign_via_api(): void
    {
        $response = $this->postJson('/kanpen-api/campaigns', [
            'name' => 'My Newsletter',
            'subject' => 'Hello Subscribers',
            'content_html' => '<p>Welcome!</p>',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('campaigns', ['name' => 'My Newsletter', 'status' => 'draft']);
    }

    #[Test]
    public function it_lists_campaigns(): void
    {
        Campaign::create(['name' => 'A', 'subject' => 'Sub A', 'status' => 'draft']);
        Campaign::create(['name' => 'B', 'subject' => 'Sub B', 'status' => 'draft']);

        $response = $this->getJson('/kanpen-api/campaigns');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_dispatches_send_campaign_job(): void
    {
        Bus::fake();

        $campaign = Campaign::create([
            'name' => 'Test',
            'subject' => 'Test',
            'content_html' => '<p>Hi</p>',
            'status' => 'draft',
        ]);

        app(SendCampaignAction::class)->execute($campaign);

        Bus::assertDispatched(SendCampaignJob::class, fn ($job) => $job->campaign->id === $campaign->id);
    }

    #[Test]
    public function it_throws_when_sending_non_draft_campaign(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $campaign = Campaign::create([
            'name' => 'Sent',
            'subject' => 'Sent',
            'status' => CampaignStatus::Sent->value,
        ]);

        app(SendCampaignAction::class)->execute($campaign);
    }

    #[Test]
    public function it_shows_campaign_stats(): void
    {
        $campaign = Campaign::create(['name' => 'Stats', 'subject' => 'Stats', 'status' => 'draft']);

        $response = $this->getJson("/kanpen-api/campaigns/{$campaign->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('stats.sent', 0);
        $response->assertJsonPath('stats.opened', 0);
    }

    #[Test]
    public function it_requires_name_and_subject_to_create_campaign(): void
    {
        $response = $this->postJson('/kanpen-api/campaigns', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'subject']);
    }

    #[Test]
    public function it_sends_a_test_email_without_affecting_campaign_status(): void
    {
        Mail::fake();

        $campaign = Campaign::create([
            'name' => 'Test Campaign',
            'subject' => 'Hello World',
            'content_html' => '<p>Body</p>',
            'status' => 'draft',
        ]);

        $response = $this->postJson("/kanpen-api/campaigns/{$campaign->id}/test", [
            'email' => 'preview@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['sent' => true]);

        // Campaign status must not change
        $campaign->refresh();
        $this->assertEquals('draft', $campaign->status->value);
        $this->assertNull($campaign->sent_at);

        // No CampaignDelivery records created
        $this->assertEquals(0, CampaignDelivery::count());

        Mail::assertSent(CampaignMail::class, fn ($mail) => $mail->hasTo('preview@example.com'));
    }

    #[Test]
    public function it_validates_email_for_test_send(): void
    {
        $campaign = Campaign::create([
            'name' => 'Test',
            'subject' => 'Test',
            'status' => 'draft',
        ]);

        $response = $this->postJson("/kanpen-api/campaigns/{$campaign->id}/test", [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_can_send_test_email_for_any_campaign_status(): void
    {
        Mail::fake();

        foreach (['draft', 'sent'] as $status) {
            $campaign = Campaign::create([
                'name' => "Campaign {$status}",
                'subject' => 'Subject',
                'content_html' => '<p>Hello</p>',
                'status' => $status,
            ]);

            $response = $this->postJson("/kanpen-api/campaigns/{$campaign->id}/test", [
                'email' => 'qa@example.com',
            ]);

            $response->assertStatus(200);
        }

        Mail::assertSentCount(2);
    }
}
