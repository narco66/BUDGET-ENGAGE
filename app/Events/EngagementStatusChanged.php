<?php

namespace App\Events;

class EngagementStatusChanged
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public int $engagementId,
        public string $from,
        public string $to,
        public ?int $triggeredBy = null,
        public array $context = [],
    ) {}
}
