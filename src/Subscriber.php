<?php

namespace Mydnic\Subscribers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Mydnic\Subscribers\Events\SubscriberCreated;
use Mydnic\Subscribers\Events\SubscriberDeleted;

class Subscriber extends Model
{
    use Notifiable;

    protected $table = 'subscribers';

    protected $fillable = [
        'email',
    ];

    protected $dispatchesEvents = [
        'created' => SubscriberCreated::class,
        'deleted' => SubscriberDeleted::class,
    ];
}
