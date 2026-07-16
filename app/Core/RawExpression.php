<?php

declare(strict_types=1);

namespace App\Core;

class RawExpression
{
    public function __construct(
        public string $sql,
        public array $bindings = []
    ) {}
}
