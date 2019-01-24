<?php

namespace Mydnic\Subscribers;

use Illuminate\Database\Eloquent\Model;
use Mydnic\Subscribers\Events\SubscriberCreated;
use Mydnic\Subscribers\Events\SubscriberDeleted;

class Subscriber extends Model
{
    protected $table = 'subscribers';

    protected $fillable = [
        'email',
    ];

    protected $dispatchesEvents = [
        'created' => SubscriberCreated::class,
        'deleted' => SubscriberDeleted::class,
    ];
}
