<?php

declare(strict_types=1);

namespace Ray\Aop;

/** @psalm-import-type MethodBindings from Types */
interface WeavedInterface
{
    /** @param MethodBindings $bindings */
    public function initState(array $bindings): void;
}
