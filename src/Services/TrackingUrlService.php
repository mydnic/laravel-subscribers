<?php

namespace Mydnic\Subscribers\Services;

use Mydnic\Subscribers\Models\CampaignSend;

class TrackingUrlService
{
    public function openPixelUrl(CampaignSend $send): string
    {
        return route('subscribers.tracking.open', ['token' => $send->token]);
    }

    public function clickProxyUrl(CampaignSend $send, string $originalUrl): string
    {
        return route('subscribers.tracking.click', [
            'token' => $send->token,
            'url' => base64_encode($originalUrl),
        ]);
    }

    public function rewriteLinks(string $html, CampaignSend $send): string
    {
        if (! config('laravel-subscribers.tracking.click', true)) {
            return $html;
        }

        return preg_replace_callback(
            '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>/i',
            function (array $matches) use ($send) {
                $original = $matches[1];

                // Skip mailto: and anchor links
                if (str_starts_with($original, 'mailto:') || str_starts_with($original, '#')) {
                    return $matches[0];
                }

                $tracked = $this->clickProxyUrl($send, $original);

                return str_replace($original, $tracked, $matches[0]);
            },
            $html
        );
    }

    public function injectTrackingPixel(string $html, CampaignSend $send): string
    {
        if (! config('laravel-subscribers.tracking.open', true)) {
            return $html;
        }

        $pixelUrl = $this->openPixelUrl($send);
        $pixel = sprintf(
            '<img src="%s" width="1" height="1" border="0" alt="" style="display:none;" />',
            $pixelUrl
        );

        if (stripos($html, '</body>') !== false) {
            return str_ireplace('</body>', $pixel . '</body>', $html);
        }

        return $html . $pixel;
    }

    public function processHtml(string $html, CampaignSend $send): string
    {
        $html = $this->rewriteLinks($html, $send);
        $html = $this->injectTrackingPixel($html, $send);

        return $html;
    }
}
