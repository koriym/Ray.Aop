<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeWeavedClass extends FakeClass implements WeavedInterface
{
    /**
     * {@inheritDoc}
     */
    public function initState(array $bindings): void {}
}
