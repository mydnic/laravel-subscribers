<?php

namespace Mydnic\Subscribers;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Mydnic\Subscribers\Events\SubscriberCreated;
use Mydnic\Subscribers\Events\SubscriberDeleted;
use Mydnic\Subscribers\Notifications\SubscriberVerifyEmail;

class Subscriber extends Model
{
    use Notifiable, SoftDeletes;

    protected $table = 'subscribers';

    protected $fillable = [
        'email',
    ];

    protected $dispatchesEvents = [
        'created' => SubscriberCreated::class,
        'deleted' => SubscriberDeleted::class,
    ];


    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new SubscriberVerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }
}
