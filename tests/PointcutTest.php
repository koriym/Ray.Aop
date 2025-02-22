<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class PointcutTest extends TestCase
{
    /**
     * Tests creating a new Pointcut instance with class matcher, method matcher and interceptors
     */
    public function testCreatePointcutInstance(): void
    {
        $pointCunt = new Pointcut(
            new BuiltinMatcher('startsWith', ['Ray']),
            new BuiltinMatcher('startsWith', ['get']),
            [new FakeInterceptor()]
        );
        $this->assertInstanceOf(Pointcut::class, $pointCunt);
    }
}
