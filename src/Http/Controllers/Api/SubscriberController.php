<?php

namespace Mydnic\Subscribers\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Mydnic\Subscribers\Http\Requests\StoreSubscriberRequest;
use Mydnic\Subscribers\Models\Subscriber;

class SubscriberController extends Controller
{
    public function __invoke(StoreSubscriberRequest $request)
    {
        Subscriber::create($request->all());

        return response()->json(['created' => true], 201);
    }
}
