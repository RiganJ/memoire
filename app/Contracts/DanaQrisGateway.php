<?php

namespace App\Contracts;

use App\Data\DanaQrisResult;
use App\Data\DanaWebhookData;
use App\Models\Payment;

interface DanaQrisGateway
{
    public function generate(Payment $payment): DanaQrisResult;

    /** @param array<string, string> $headers */
    public function parseWebhook(string $method, string $path, array $headers, string $body): DanaWebhookData;
}
