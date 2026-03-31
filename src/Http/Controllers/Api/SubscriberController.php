<?php

namespace Mydnic\Kanpen\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Mydnic\Kanpen\Http\Requests\StoreSubscriberRequest;
use Mydnic\Kanpen\Models\Subscriber;

class SubscriberController extends Controller
{
    public function __invoke(StoreSubscriberRequest $request)
    {
        $subscriber = Subscriber::create($request->validated());

        if (config('kanpen.verify')) {
            $subscriber->sendEmailVerificationNotification();

            return response()->json(['created' => true, 'verification_required' => true], 201);
        }

        return response()->json(['created' => true], 201);
    }
}
