<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Override;
use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

use function str_starts_with;

final class StartsWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var Arguments $arguments */
        [$startsWith] = $arguments;

        return str_starts_with($class->name, $startsWith);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        /** @var Arguments $arguments */
        [$startsWith] = $arguments;

        return str_starts_with($method->name, $startsWith);
    }
}
