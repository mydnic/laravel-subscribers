<?php

namespace Mydnic\Subscribers\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Mydnic\Subscribers\Subscriber;
use Illuminate\Support\Facades\Storage;
use Mydnic\Subscribers\Events\NewSubscriber;
use Mydnic\Subscribers\Http\Requests\StoreSubscriberRequest;
use Mydnic\Subscribers\Events\SubscriberCreated;

class SubscriberController extends Controller
{
    public function __invoke(StoreSubscriberRequest $request)
    {
        $subscriber = Subscriber::create($request->all());

        return response()->json([
            'created' => true,
        ], 201);
    }
}
