<?php

namespace Mydnic\Kanpen\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Mydnic\Kanpen\Events\SubscriberVerified;
use Mydnic\Kanpen\Exceptions\SubscriberVerificationException;
use Mydnic\Kanpen\Http\Requests\StoreSubscriberRequest;
use Mydnic\Kanpen\Http\Requests\VerifySubscriberRequest;
use Mydnic\Kanpen\Models\Subscriber;

class SubscriberController extends Controller
{
    public function store(StoreSubscriberRequest $request)
    {
        $subscriber = Subscriber::create($request->validated());

        if (config('kanpen.verify')) {
            $subscriber->sendEmailVerificationNotification();

            return redirect()->route(config('kanpen.redirect_url'))
                ->with('subscribed', __('Please verify your email address!'));
        }

        return redirect()->route(config('kanpen.redirect_url'))
            ->with('subscribed', __('You are successfully subscribed to our list!'));
    }

    public function unsubscribeByToken(string $token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if ($subscriber) {
            $subscriber->delete();
        }

        return view('kanpen::subscriber.deleted');
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
                : redirect()->route(config('kanpen.redirect_url'));
        }

        if ($subscriber->markEmailAsVerified()) {
            event(new SubscriberVerified($subscriber));
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect()->route(config('kanpen.redirect_url'))
                ->with('verified', __('You are successfully subscribed to our list!'));
    }
}
