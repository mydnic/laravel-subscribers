<?php

namespace Mydnic\Subscribers\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
    case Cancelled = 'cancelled';
}
