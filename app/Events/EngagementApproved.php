<?php

namespace App\Events;

class EngagementApproved
{
    public function __construct(
        public int $engagementId
    ) {}
}
