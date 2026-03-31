<?php

namespace Mydnic\Kanpen\Traits;

use Illuminate\Database\Eloquent\Builder;
use Mydnic\Kanpen\Models\Subscriber;

/**
 * Add this trait to your User model (or any Eloquent model with an email column)
 * to automatically sync it with the subscribers table.
 *
 * You must implement shouldBeSubscribed() on your model to define the subscription condition:
 *
 *   public function shouldBeSubscribed(): bool
 *   {
 *       return $this->subscribed_to_newsletter && $this->email_verified_at !== null;
 *   }
 */
trait HasNewsletterSubscription
{
    abstract public function shouldBeSubscribed(): bool;

    public static function bootHasNewsletterSubscription(): void
    {
        static::saved(function ($model) {
            $model->syncSubscriberRecord();
        });

        static::deleted(function ($model) {
            if (! method_exists($model, 'isForceDeleting') || $model->isForceDeleting()) {
                Subscriber::where('email', $model->getSubscriberEmail())->forceDelete();
            } else {
                Subscriber::where('email', $model->getSubscriberEmail())->delete();
            }
        });
    }

    public function syncSubscriberRecord(): void
    {
        $email = $this->getSubscriberEmail();

        if (empty($email)) {
            return;
        }

        if ($this->shouldBeSubscribed()) {
            $existing = Subscriber::withTrashed()->where('email', $email)->first();

            if ($existing && $existing->trashed()) {
                $existing->restore();
            } elseif (! $existing) {
                Subscriber::create(['email' => $email]);
            }
        } else {
            Subscriber::where('email', $email)->delete();
        }
    }

    public function getSubscriberEmail(): string
    {
        return (string) ($this->email ?? '');
    }

    public function subscriber(): ?Builder
    {
        return Subscriber::where('email', $this->getSubscriberEmail());
    }

    public function subscribe(): void
    {
        $email = $this->getSubscriberEmail();
        $subscriber = Subscriber::withTrashed()->where('email', $email)->first();

        if ($subscriber && $subscriber->trashed()) {
            $subscriber->restore();
        } elseif (! $subscriber) {
            $subscriber = Subscriber::create(['email' => $email]);
        }

        if (config('kanpen.verify') && ! $subscriber->hasVerifiedEmail()) {
            $subscriber->sendEmailVerificationNotification();
        }
    }

    public function unsubscribe(): void
    {
        Subscriber::where('email', $this->getSubscriberEmail())->delete();
    }

    public function isSubscribed(): bool
    {
        return Subscriber::where('email', $this->getSubscriberEmail())->exists();
    }
}
