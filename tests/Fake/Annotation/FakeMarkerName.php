<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class FakeMarkerName
{
    public int $a;
    public string $b;
    public bool $c;

    public function __construct(int $a, string $b, bool $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}
