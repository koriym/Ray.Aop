<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\Exception\InvalidArgumentException;
use ReflectionException;

class MatcherTest extends TestCase
{
    /**
     * Tests returning various built-in matchers including any, annotatedWith, logicalAnd, logicalOr, logicalNot, startsWith, and subclassesOf
     *
     * @throws ReflectionException
     */
    public function testReturnBuiltInMatchers(): void
    {
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->any());
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->annotatedWith(FakeResource::class));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalAnd(new FakeMatcher(), new FakeMatcher()));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalAnd(new FakeMatcher(), new FakeMatcher(), new FakeMatcher()));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalOr(new FakeMatcher(), new FakeMatcher(false)));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalOr(new FakeMatcher(), new FakeMatcher(), new FakeMatcher(false)));

        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalNot(new FakeMatcher()));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->startsWith('a'));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->subclassesOf(FakeClass::class));
    }

    /**
     * Tests that InvalidAnnotationException is thrown when annotatedWith matcher receives invalid class name
     *
     * @throws ReflectionException
     */
    public function testThrowExceptionForInvalidAnnotatedWith(): void
    {
        $this->expectException(InvalidAnnotationException::class);

        (new Matcher())->annotatedWith('__invalid_class');
    }

    /**
     * Tests that InvalidArgumentException is thrown when subclassesOf matcher receives invalid class name
     *
     * @throws ReflectionException
     */
    public function testThrowExceptionForInvalidSubclassesOf(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Matcher())->subclassesOf('__invalid_class');
    }
}
