<?php

namespace Mydnic\Subscribers\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Mydnic\Subscribers\Actions\SendCampaignAction;
use Mydnic\Subscribers\Actions\SendTestCampaignAction;
use Mydnic\Subscribers\Http\Requests\SendTestCampaignRequest;
use Mydnic\Subscribers\Http\Requests\StoreCampaignRequest;
use Mydnic\Subscribers\Http\Requests\UpdateCampaignRequest;
use Mydnic\Subscribers\Models\Campaign;

class CampaignController extends Controller
{
    public function index(): JsonResponse
    {
        $campaigns = Campaign::withCount('sends')
            ->latest()
            ->paginate(15);

        return response()->json($campaigns);
    }

    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = Campaign::create($request->validated());

        return response()->json($campaign, 201);
    }

    public function show(Campaign $campaign): JsonResponse
    {
        $campaign->loadCount(['sends'])
            ->load('sends');

        $openCount = $campaign->sends->whereNotNull('opened_at')->count();
        $clickCount = $campaign->sends->whereNotNull('clicked_at')->count();
        $sendCount = $campaign->sends->whereNotNull('sent_at')->count();

        return response()->json([
            'campaign' => $campaign,
            'stats' => [
                'sent' => $sendCount,
                'opened' => $openCount,
                'clicked' => $clickCount,
                'open_rate' => $sendCount > 0 ? round($openCount / $sendCount * 100, 2) : 0,
                'click_rate' => $sendCount > 0 ? round($clickCount / $sendCount * 100, 2) : 0,
            ],
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): JsonResponse
    {
        $campaign->update($request->validated());

        return response()->json($campaign);
    }

    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->delete();

        return response()->json(null, 204);
    }

    public function send(Campaign $campaign, SendCampaignAction $action): JsonResponse
    {
        $action->execute($campaign);

        return response()->json(['dispatched' => true]);
    }

    public function test(SendTestCampaignRequest $request, Campaign $campaign, SendTestCampaignAction $action): JsonResponse
    {
        $action->execute($campaign, $request->validated('email'));

        return response()->json(['sent' => true]);
    }
}
