<?php

namespace Mydnic\Kanpen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignClick extends Model
{
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('kanpen.tables.campaign_clicks'));
    }

    protected $fillable = [
        'campaign_delivery_id',
        'url',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(CampaignDelivery::class, 'campaign_delivery_id');
    }
}
