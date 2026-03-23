<?php

namespace Mydnic\Subscribers\Test;

use Illuminate\Support\Facades\Event;
use Mydnic\Subscribers\Events\SubscriberCreated;
use Mydnic\Subscribers\Events\SubscriberDeleted;
use Mydnic\Subscribers\Models\Subscriber;
use PHPUnit\Framework\Attributes\Test;

class SubscriberTest extends TestCase
{
    #[Test]
    public function it_saves_the_subscriber_via_api(): void
    {
        Event::fake([SubscriberCreated::class]);

        $response = $this->post('/subscribers-api/subscriber', [
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

        $response = $this->post('/subscribers/subscriber', [
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

        $this->post('/subscribers-api/subscriber', ['email' => 'some@email.com']);

        $this->assertEquals(1, Subscriber::count());
    }

    #[Test]
    public function it_unsubscribes_via_token(): void
    {
        Event::fake([SubscriberDeleted::class]);

        $subscriber = Subscriber::create(['email' => 'some@email.com']);

        $response = $this->get("/subscribers/unsubscribe/{$subscriber->unsubscribe_token}");

        $response->assertStatus(200);
        $this->assertEquals(0, Subscriber::count());

        Event::assertDispatched(SubscriberDeleted::class);
    }

    #[Test]
    public function it_shows_unsubscribed_page_for_unknown_token(): void
    {
        // Unknown token silently returns 200 — no email enumeration possible
        $response = $this->get('/subscribers/unsubscribe/totally-fake-token');

        $response->assertStatus(200);
    }
}
