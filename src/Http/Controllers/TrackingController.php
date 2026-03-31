<?php

namespace Mydnic\Kanpen\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Mydnic\Kanpen\Events\EmailLinkClicked;
use Mydnic\Kanpen\Events\EmailOpened;
use Mydnic\Kanpen\Models\CampaignClick;
use Mydnic\Kanpen\Models\CampaignDelivery;

class TrackingController extends Controller
{
    // 1x1 transparent GIF in binary
    private const PIXEL_GIF = "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff\x21\xf9\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x01\x44\x00\x3b";

    public function open(string $token): Response
    {
        $send = CampaignDelivery::where('token', $token)->first();

        if ($send) {
            $send->increment('open_count');

            if (is_null($send->opened_at)) {
                $send->update(['opened_at' => now()]);
            }

            EmailOpened::dispatch($send);
        }

        return response(self::PIXEL_GIF, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function click(Request $request, string $token): RedirectResponse
    {
        $send = CampaignDelivery::where('token', $token)->first();

        $encodedUrl = $request->query('url', '');
        $url = base64_decode($encodedUrl, strict: true);

        if ($url === false || empty($url)) {
            abort(400, 'Invalid tracking URL.');
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], strict: true)) {
            abort(400, 'Invalid tracking URL.');
        }

        $allowedDomains = config('kanpen.tracking.allowed_domains', []);
        if (! empty($allowedDomains)) {
            $host = parse_url($url, PHP_URL_HOST);
            if (! in_array($host, $allowedDomains, strict: true)) {
                abort(403, 'Domain not allowed.');
            }
        }

        if ($send) {
            CampaignClick::create([
                'campaign_delivery_id' => $send->id,
                'url' => $url,
                'clicked_at' => now(),
            ]);

            $send->update(['clicked_at' => $send->clicked_at ?? now()]);

            EmailLinkClicked::dispatch($send, $url);
        }

        return redirect($url);
    }
}
