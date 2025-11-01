<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Override;
use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

use function assert;

final class AnnotatedWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        assert($class instanceof \Ray\Aop\ReflectionClass);
        /** @var Arguments $arguments */
        [$annotation] = $arguments;
        /** @psalm-suppress MixedAssignment $annotation */
        $annotation = $class->getAnnotation($annotation);

        return (bool) $annotation;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        assert($method instanceof \Ray\Aop\ReflectionMethod);
        /** @var Arguments $arguments */
        [$annotation] = $arguments;

        $annotation = $method->getAnnotation($annotation);

        return (bool) $annotation;
    }
}
