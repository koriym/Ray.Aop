<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class FakeMarker4
{
    private array $a;
    private int $b;

    public function __construct(array $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
