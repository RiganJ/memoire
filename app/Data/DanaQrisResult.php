<?php

namespace App\Data;

final readonly class DanaQrisResult
{
    public function __construct(
        public string $referenceNo,
        public string $qrContent,
        public ?string $qrUrl,
        public ?string $qrImage,
    ) {}
}
