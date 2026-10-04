<?php

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class DanaWebhookData
{
    public function __construct(
        public string $partnerReferenceNo,
        public ?string $referenceNo,
        public string $merchantId,
        public string $amount,
        public string $currency,
        public string $status,
        public ?CarbonImmutable $finishedAt,
    ) {}
}
