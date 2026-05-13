<?php

namespace Mydnic\Kanpen\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mydnic\Kanpen\Models\Campaign;
use Mydnic\Kanpen\Models\CampaignDelivery;
use Mydnic\Kanpen\Services\TrackingUrlService;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Campaign $campaign,
        public readonly CampaignDelivery $send,
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = $this->campaign->from_email
            ?? config('kanpen.campaigns.from.email')
            ?? config('mail.from.address');

        $fromName = $this->campaign->from_name
            ?? config('kanpen.campaigns.from.name')
            ?? config('mail.from.name');

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            replyTo: $this->campaign->reply_to ? [new Address($this->campaign->reply_to)] : [],
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        $html = view('kanpen::mail.campaign', [
            'campaign' => $this->campaign,
            'send' => $this->send,
            'subscriber' => $this->send->subscriber,
            'contentHtml' => $this->campaign->content_html ?? '',
        ])->render();

        if (config('kanpen.tracking.enabled', true)) {
            $html = app(TrackingUrlService::class)->processHtml($html, $this->send);
        }

        return new Content(htmlString: $html);
    }

    public function build(): static
    {
        return $this;
    }
}
