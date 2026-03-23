<?php

namespace Mydnic\Subscribers\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Mydnic\Subscribers\Events\SubscriberVerified;
use Mydnic\Subscribers\Exceptions\SubscriberVerificationException;
use Mydnic\Subscribers\Http\Requests\StoreSubscriberRequest;
use Mydnic\Subscribers\Http\Requests\VerifySubscriberRequest;
use Mydnic\Subscribers\Models\Subscriber;

class SubscriberController extends Controller
{
    public function store(StoreSubscriberRequest $request)
    {
        $subscriber = Subscriber::create($request->validated());

        if (config('laravel-subscribers.verify')) {
            $subscriber->sendEmailVerificationNotification();

            return redirect()->route(config('laravel-subscribers.redirect_url'))
                ->with('subscribed', __('Please verify your email address!'));
        }

        return redirect()->route(config('laravel-subscribers.redirect_url'))
            ->with('subscribed', __('You are successfully subscribed to our list!'));
    }

    public function unsubscribeByToken(string $token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if ($subscriber) {
            $subscriber->delete();
        }

        return view('laravel-subscribers::subscriber.deleted');
    }

    public function verify(VerifySubscriberRequest $request)
    {
        $subscriber = Subscriber::find($request->id);

        if (! hash_equals((string) $request->route('id'), (string) $subscriber->getKey())) {
            throw new SubscriberVerificationException;
        }

        if (! hash_equals((string) $request->route('hash'), sha1($subscriber->getEmailForVerification()))) {
            throw new SubscriberVerificationException;
        }

        if ($subscriber->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new Response('', 204)
                : redirect()->route(config('laravel-subscribers.redirect_url'));
        }

        if ($subscriber->markEmailAsVerified()) {
            event(new SubscriberVerified($subscriber));
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect()->route(config('laravel-subscribers.redirect_url'))
                ->with('verified', __('You are successfully subscribed to our list!'));
    }
}
