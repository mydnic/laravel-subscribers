<?php

namespace Mydnic\Kanpen\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class SubscriberVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $mail = new MailMessage;

        $mail->subject(Lang::get(config('kanpen.mail.verify.subject', 'Verify Email Address')));
        $mail->greeting(Lang::get(config('kanpen.mail.verify.greeting', 'Hello!')));

        if (! empty(config('kanpen.mail.verify.content'))) {
            foreach (config('kanpen.mail.verify.content') as $value) {
                $mail->line(Lang::get($value));
            }
        } else {
            $mail->line(Lang::get('Please click the button below to verify your email address.'));
        }

        $mail->action(Lang::get(config('kanpen.mail.verify.action', 'Verify Email Address')), $verificationUrl);

        if (! empty(config('kanpen.mail.verify.footer'))) {
            foreach (config('kanpen.mail.verify.footer') as $value) {
                $mail->line(Lang::get($value));
            }
        } else {
            $mail->line(Lang::get('If you did not sign up for our newsletter, no further action is required.'));
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'kanpen.verify',
            Carbon::now()->addMinutes(config('kanpen.mail.verify.expiration')),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
