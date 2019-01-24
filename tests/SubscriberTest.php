<?php

namespace Mydnic\Subscribers\Test;

use Mydnic\Subscribers\Subscriber;
use Illuminate\Support\Facades\Event;
use Mydnic\Subscribers\Events\SubscriberCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Http\Middleware\VerifyCsrfToken;
use Mydnic\Subscribers\Events\SubscriberDeleted;

class SubscriberTest extends TestCase
{
    /** @test */
    public function it_saves_the_subscriber_via_api()
    {
        Event::fake();

        $request = $this->post('/subscribers-api/subscriber', [
            'email' => 'some@email.com',
        ]);

        $request->assertStatus(201);

        $subscriber = Subscriber::first();
        $this->assertEquals('some@email.com', $subscriber->email);

        Event::assertDispatched(SubscriberCreated::class, function ($e) use ($subscriber) {
            return $e->subscriber->id === $subscriber->id;
        });
    }

    /** @test */
    public function it_saves_the_subscriber_via_web()
    {
        Event::fake();

        $request = $this->post('/subscribers/subscriber', [
            'email' => 'someweb@email.com',
        ]);

        $request->assertStatus(302);

        $subscriber = Subscriber::first();
        $this->assertEquals('someweb@email.com', $subscriber->email);

        Event::assertDispatched(SubscriberCreated::class, function ($e) use ($subscriber) {
            return $e->subscriber->id === $subscriber->id;
        });
    }

    /** @test */
    public function it_refuses_existing_subscribers()
    {
        Subscriber::create(['email' => 'some@email.com']);

        $request = $this->post('/subscribers-api/subscriber', [
            'email' => 'some@email.com',
        ]);

        $this->assertEquals(1, Subscriber::count());
    }

    /** @test */
    public function it_deletes_existing_subscribers()
    {
        Event::fake();

        Subscriber::create(['email' => 'some@email.com']);

        $request = $this->get('/subscribers/delete?email=some@email.com');

        $this->assertEquals(0, Subscriber::count());

        Event::assertDispatched(SubscriberDeleted::class);
    }
}
