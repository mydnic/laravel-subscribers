<?php

namespace Mydnic\Subscribers\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Mydnic\Subscribers\Events\SubscriberVerified;
use Mydnic\Subscribers\Subscriber;
use Mydnic\Subscribers\Http\Requests\StoreSubscriberRequest;
use Mydnic\Subscribers\Http\Requests\DeleteSubscriberRequest;
use Mydnic\Subscribers\Http\Requests\VerifySubscriberRequest;
use Mydnic\Subscribers\Exceptions\SubscriberVerificationException;

class SubscriberController extends Controller
{
    public function store(StoreSubscriberRequest $request)
    {
        $subscriber = Subscriber::create($request->all());

        if(config('laravel-subscribers.verify'))
        {
            $subscriber->sendEmailVerificationNotification();
            return back()->with('subscribed', 'Please verify your email address!');
        }

        return back()->with('subscribed', 'You are successfully subscribed to our list!');
    }

    public function delete(DeleteSubscriberRequest $request)
    {
        $request->subscriber()->delete();
        return view('subscribe.deleted');
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
                : redirect($this->redirectPath());
        }

        if ($subscriber->markEmailAsVerified()) {
            event(new SubscriberVerified($subscriber));
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect()->route(config('laravel-subscribers.redirect_url'));
    }
}
