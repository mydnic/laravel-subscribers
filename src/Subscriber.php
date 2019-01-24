<?php

namespace Mydnic\Subscribers;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscribers';

    protected $fillable = [
        'email',
    ];

    protected $dispatchesEvents = [
        'saved' => SubscriberCreated::class,
        'deleted' => SubscriberDeleted::class,
    ];
}
