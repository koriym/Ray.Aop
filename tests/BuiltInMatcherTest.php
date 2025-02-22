<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidMatcherException;
use ReflectionClass;
use ReflectionMethod;

class BuiltInMatcherTest extends TestCase
{
    /** @var BuiltinMatcher */
    private $matcher;

    protected function setUp(): void
    {
        $this->matcher = new BuiltinMatcher('startsWith', ['Ray']);
    }

    /**
     * Tests class name matching using startsWith built-in matcher
     */
    public function testMatchClassWithStartsWithMatcher(): void
    {
        $class = new ReflectionClass(FakeClass::class);
        $isMatched = $this->matcher->matchesClass($class, ['Ray\Aop']);
        $this->assertTrue($isMatched);
    }

    /**
     * Tests method name matching using startsWith built-in matcher
     */
    public function testMatchMethodWithStartsWithMatcher(): void
    {
        $method = new ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = $this->matcher->matchesMethod($method, ['get']);
        $this->assertTrue($isMatched);
    }

    /**
     * Tests that InvalidMatcherException is thrown when using invalid built-in matcher
     */
    public function testThrowExceptionForInvalidBuiltinMatcher(): void
    {
        $this->expectException(InvalidMatcherException::class);
        new BuiltinMatcher('invalid', []);
    }
}
