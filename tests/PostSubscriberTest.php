<?php

namespace Mydnic\Subscribers\Test;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Http\Middleware\VerifyCsrfToken;
use Mydnic\Subscribers\Events\NewSubscriber;
use Mydnic\Subscribers\SubscriberCreated;

class PostSubscriberTest extends TestCase
{
    /** @test */
    public function it_saves_the_subscriber()
    {
        Event::fake();

        $request = $this->post('/subscribers-api/subscriber', [
            'email' => 'some@email.com',
        ]);

        // Help debug Request
        // dd($request->original);

        $request->assertStatus(201);

        $subscriber = Feedback::first();
        $this->assertEquals(false, $subscriber->reviewed);
        $this->assertEquals('like', $subscriber->type);
        $this->assertEquals('test subscriber message', $subscriber->message);

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

        dd($request->original);
    }
}
