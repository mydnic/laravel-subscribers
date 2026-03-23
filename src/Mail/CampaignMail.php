<?php

namespace Mydnic\Subscribers\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mydnic\Subscribers\Models\Campaign;
use Mydnic\Subscribers\Models\CampaignSend;
use Mydnic\Subscribers\Services\TrackingUrlService;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Campaign $campaign,
        public readonly CampaignSend $send,
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = $this->campaign->from_email
            ?? config('laravel-subscribers.campaigns.from.email')
            ?? config('mail.from.address');

        $fromName = $this->campaign->from_name
            ?? config('laravel-subscribers.campaigns.from.name')
            ?? config('mail.from.name');

        $envelope = new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: $this->campaign->subject,
        );

        if ($this->campaign->reply_to) {
            $envelope = new Envelope(
                from: new Address($fromEmail, $fromName),
                replyTo: [new Address($this->campaign->reply_to)],
                subject: $this->campaign->subject,
            );
        }

        return $envelope;
    }

    public function content(): Content
    {
        if ($this->campaign->view) {
            return new Content(
                view: $this->campaign->view,
                with: [
                    'campaign' => $this->campaign,
                    'send' => $this->send,
                    'subscriber' => $this->send->subscriber,
                ],
            );
        }

        return new Content(
            view: 'laravel-subscribers::mail.campaign',
            with: [
                'campaign' => $this->campaign,
                'send' => $this->send,
                'subscriber' => $this->send->subscriber,
                'contentHtml' => $this->campaign->content_html ?? '',
            ],
        );
    }

    public function build(): static
    {
        return $this;
    }

    /**
     * Process the rendered HTML to inject tracking before the mail is sent.
     */
    public function render(): string
    {
        $html = parent::render();

        if (config('laravel-subscribers.tracking.enabled', true)) {
            $trackingService = app(TrackingUrlService::class);
            $html = $trackingService->processHtml($html, $this->send);
        }

        return $html;
    }
}
