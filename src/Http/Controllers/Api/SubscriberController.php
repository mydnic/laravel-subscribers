<?php

namespace Mydnic\Subscribers\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Mydnic\Subscribers\Subscriber;
use Illuminate\Support\Facades\Storage;
use Mydnic\Subscribers\Events\NewSubscriber;
use Mydnic\Subscribers\Http\Requests\StoreSubscriberRequest;

class SubscriberController extends Controller
{
    public function __invoke(StoreSubscriberRequest $request)
    {
        Subscriber::create($request->all());

        return back();
    }
}
