<?php

namespace App\DataTransferObjects;

readonly class DigikalaSyncResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $message = null,
        public ?int $price = null,
    ) {}
}
