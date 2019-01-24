<?php

namespace Mydnic\Subscribers\Test;

use Mydnic\Subscribers\Subscriber;
use Illuminate\Support\Facades\Event;
use Mydnic\Subscribers\Events\SubscriberCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Http\Middleware\VerifyCsrfToken;

class SubscriberTest extends TestCase
{
    /** @test */
    public function it_saves_the_subscriber()
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
    public function it_refuses_existing_subscribers()
    {
        Subscriber::create(['email' => 'some@email.com']);

        $request = $this->post('/subscribers-api/subscriber', [
            'email' => 'some@email.com',
        ]);

        $this->assertEquals(1, Subscriber::count());
    }
}
