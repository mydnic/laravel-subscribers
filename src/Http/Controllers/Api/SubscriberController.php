<?php

namespace Mydnic\Kanpen\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Mydnic\Kanpen\Http\Requests\StoreSubscriberRequest;
use Mydnic\Kanpen\Models\Subscriber;

class SubscriberController extends Controller
{
    public function __invoke(StoreSubscriberRequest $request)
    {
        Subscriber::create($request->all());

        return response()->json(['created' => true], 201);
    }
}
