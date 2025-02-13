<?php

declare(strict_types=1);

namespace Ray\Aop;

interface WeavedInterface
{
    /** @param array<string, array<MethodInterceptor|string>> $bindings */
    public function initState(array $bindings): void;
}
