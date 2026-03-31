<?php

namespace Mydnic\Kanpen\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
    case Cancelled = 'cancelled';
}
