<?php

namespace Mydnic\Kanpen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mydnic\Kanpen\Enums\CampaignStatus;

class Campaign extends Model
{
    use SoftDeletes;

    protected $table = 'campaigns';

    protected $fillable = [
        'name',
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'content_html',
        'view',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function sends(): HasMany
    {
        return $this->hasMany(CampaignDelivery::class);
    }

    public function isDraft(): bool
    {
        return $this->status === CampaignStatus::Draft;
    }

    public function isSending(): bool
    {
        return $this->status === CampaignStatus::Sending;
    }

    public function isSent(): bool
    {
        return $this->status === CampaignStatus::Sent;
    }
}
