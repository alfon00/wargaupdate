<?php

namespace App\Support;

class FaceMatchResult
{
    public function __construct(
        public readonly bool $matched,
        public readonly float $distance,
        public readonly string $source,
        public readonly ?int $referenceDocumentId = null,
        public readonly ?int $faceIndex = null,
    ) {}
}
