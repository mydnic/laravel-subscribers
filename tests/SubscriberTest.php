<?php

namespace Mydnic\Kanpen\Test;

use Illuminate\Support\Facades\Event;
use Mydnic\Kanpen\Events\SubscriberCreated;
use Mydnic\Kanpen\Events\SubscriberDeleted;
use Mydnic\Kanpen\Events\SubscriberVerified;
use Mydnic\Kanpen\Models\Subscriber;
use PHPUnit\Framework\Attributes\Test;

class SubscriberTest extends TestCase
{
    #[Test]
    public function it_saves_the_subscriber_via_api(): void
    {
        Event::fake([SubscriberCreated::class]);

        $response = $this->post('/kanpen-api/subscriber', [
            'email' => 'some@email.com',
        ]);

        $response->assertStatus(201);

        $subscriber = Subscriber::first();
        $this->assertEquals('some@email.com', $subscriber->email);

        Event::assertDispatched(SubscriberCreated::class, fn ($e) => $e->subscriber->id === $subscriber->id);
    }

    #[Test]
    public function it_saves_the_subscriber_via_web(): void
    {
        Event::fake([SubscriberCreated::class]);

        $response = $this->post('/kanpen/subscriber', [
            'email' => 'someweb@email.com',
        ]);

        $response->assertStatus(302);

        $subscriber = Subscriber::first();
        $this->assertEquals('someweb@email.com', $subscriber->email);

        Event::assertDispatched(SubscriberCreated::class, fn ($e) => $e->subscriber->id === $subscriber->id);
    }

    #[Test]
    public function it_generates_an_unsubscribe_token_on_creation(): void
    {
        $subscriber = Subscriber::create(['email' => 'some@email.com']);

        $this->assertNotEmpty($subscriber->unsubscribe_token);
        $this->assertEquals(64, strlen($subscriber->unsubscribe_token));
    }

    #[Test]
    public function it_refuses_existing_subscribers(): void
    {
        Subscriber::create(['email' => 'some@email.com']);

        $this->post('/kanpen-api/subscriber', ['email' => 'some@email.com']);

        $this->assertEquals(1, Subscriber::count());
    }

    #[Test]
    public function it_rejects_an_email_with_a_non_existent_domain(): void
    {
        // .invalid is reserved by RFC 2606 and is guaranteed to never resolve
        $response = $this->postJson('/kanpen-api/subscriber', [
            'email' => 'someone@this-domain-does-not-exist.invalid',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertEquals(0, Subscriber::count());
    }

    #[Test]
    public function it_swallows_notification_failures_when_sending_verification_email(): void
    {
        $subscriber = new class extends Subscriber
        {
            public function notify($instance): void
            {
                throw new \RuntimeException('Cloudflare rejected the address');
            }
        };
        $subscriber->forceFill(['email' => 'some@email.com']);

        $subscriber->sendEmailVerificationNotification();

        $this->assertTrue(true);
    }

    #[Test]
    public function it_unsubscribes_via_token(): void
    {
        Event::fake([SubscriberDeleted::class]);

        $subscriber = Subscriber::create(['email' => 'some@email.com']);

        $response = $this->get("/kanpen/unsubscribe/{$subscriber->unsubscribe_token}");

        $response->assertStatus(200);
        $this->assertEquals(0, Subscriber::count());

        Event::assertDispatched(SubscriberDeleted::class);
    }

    #[Test]
    public function it_shows_unsubscribed_page_for_unknown_token(): void
    {
        // Unknown token silently returns 200 — no email enumeration possible
        $response = $this->get('/kanpen/unsubscribe/totally-fake-token');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_verifies_a_subscriber_with_a_valid_hash(): void
    {
        Event::fake([SubscriberVerified::class]);

        $subscriber = Subscriber::create(['email' => 'some@email.com']);
        $hash = sha1($subscriber->getEmailForVerification());

        $response = $this->get("/kanpen/verify/{$subscriber->id}/{$hash}");

        $response->assertRedirect();
        $this->assertTrue($subscriber->fresh()->hasVerifiedEmail());

        Event::assertDispatched(SubscriberVerified::class);
    }

    #[Test]
    public function it_returns_404_for_an_invalid_verification_hash(): void
    {
        $subscriber = Subscriber::create(['email' => 'some@email.com']);

        $response = $this->get("/kanpen/verify/{$subscriber->id}/not-the-real-hash");

        $response->assertStatus(404);
        $this->assertFalse($subscriber->fresh()->hasVerifiedEmail());
    }
}
