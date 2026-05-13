@php
    $isFullDocument = str_starts_with(ltrim($contentHtml), '<!DOCTYPE') || str_starts_with(ltrim($contentHtml), '<html');
@endphp

@if($isFullDocument)
    {!! $contentHtml !!}
@else
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ $campaign->subject ?? '' }}</title>
        <style>
            body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333333; background-color: #f4f4f4; }
            .wrapper { width: 100%; background-color: #f4f4f4; padding: 40px 0; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; }
            .body { padding: 40px; }
            .footer { border-top: 1px solid #e8e8e8; padding: 20px 40px; text-align: center; font-size: 13px; color: #999999; }
            .footer a { color: #999999; }
            a { color: #4a90e2; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="container">
                <div class="body">
                    {!! $contentHtml !!}
                </div>
                @isset($subscriber)
                    <div class="footer">
                        You are receiving this email because you subscribed.<br>
                        <a href="{{ $subscriber->getUnsubscribeUrl() }}">Unsubscribe</a>
                    </div>
                @endisset
            </div>
        </div>
    </body>
    </html>
@endif
