<?php

namespace Mydnic\Kanpen\Traits;

use Illuminate\Database\Eloquent\Builder;
use Mydnic\Kanpen\Models\Subscriber;

/**
 * Add this trait to your User model (or any Eloquent model with an email column)
 * to integrate it with the subscribers table automatically.
 *
 * The trait observes model saves and syncs the subscriber record based on the
 * value of a boolean column (default: `subscribed_to_newsletter`).
 *
 * Usage in your model:
 *
 *   use HasNewsletterSubscription;
 *
 * Optionally override the defaults:
 *
 *   protected string $subscriberColumn = 'wants_newsletter';
 *   protected string $subscriberEmailColumn = 'email';
 */
trait HasNewsletterSubscription
{
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

        if ($this->wantsNewsletter()) {
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

    public function wantsNewsletter(): bool
    {
        $column = $this->getSubscriberColumn();

        return (bool) ($this->{$column} ?? false);
    }

    public function getSubscriberColumn(): string
    {
        return property_exists($this, 'subscriberColumn')
            ? $this->subscriberColumn
            : 'subscribed_to_newsletter';
    }

    public function getSubscriberEmailColumn(): string
    {
        return property_exists($this, 'subscriberEmailColumn')
            ? $this->subscriberEmailColumn
            : 'email';
    }

    public function getSubscriberEmail(): string
    {
        return (string) ($this->{$this->getSubscriberEmailColumn()} ?? '');
    }

    public function subscriber(): ?Builder
    {
        return Subscriber::where('email', $this->getSubscriberEmail());
    }
}
