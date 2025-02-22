<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeClassMarker;
use Ray\Aop\Annotation\FakeMarker;
use ReflectionMethod;

class ReflectiveMethodInvocationTest extends TestCase
{
    /** @var ReflectiveMethodInvocation<FakeClass> */
    protected $invocation;

    /** @var FakeClass */
    protected $fake;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fake = new FakeClass();
        $this->invocation = new ReflectiveMethodInvocation($this->fake, 'add', [1]);
    }

    /**
     * Tests that getMethod() returns a ReflectionMethod instance
     */
    public function testGetMethodReturnsReflectionMethod(): void
    {
        $methodReflection = $this->invocation->getMethod();
        $this->assertInstanceOf(ReflectionMethod::class, $methodReflection);
    }

    /**
     * Tests that getMethod() returns correct class and method name
     */
    public function testGetMethodReturnsCorrectClassAndMethodName(): void
    {
        $methodReflection = $this->invocation->getMethod();
        $this->assertSame(FakeClass::class, $methodReflection->class);
        $this->assertSame('add', $methodReflection->name);
    }

    /**
     * Tests that getArguments() returns correct method arguments
     */
    public function testGetArgumentsReturnsCorrectArguments(): void
    {
        $args = $this->invocation->getArguments();
        $this->assertSame((array) $args, [1]);
    }

    /**
     * Tests that proceed() executes the target method once
     */
    public function testProceedExecutesMethodOnce(): void
    {
        $this->invocation->proceed();
        $this->assertSame(1, $this->fake->a);
    }

    /**
     * Tests that proceed() can execute the target method multiple times
     */
    public function testProceedExecutesMethodTwice(): void
    {
        $this->invocation->proceed();
        $this->invocation->proceed();
        $this->assertSame(2, $this->fake->a);
    }

    /**
     * Tests that getThis() returns the target object instance
     */
    public function testGetThisReturnsTargetObject(): void
    {
        $actual = $this->invocation->getThis();
        $this->assertSame($this->fake, $actual);
    }

    /**
     * Tests that getMethod() returns parent class method for weaved classes
     */
    public function testGetParentMethodReturnsParentClassMethod(): void
    {
        $fake = new FakeWeavedClass();
        $invocation = new ReflectiveMethodInvocation($fake, 'add', [1]);
        $method = $invocation->getMethod();
        $this->assertSame(FakeClass::class, $method->class);
        $this->assertSame('add', $method->name);
    }

    /**
     * Tests that proceed() works with multiple interceptors
     */
    public function testProceedWithMultipleInterceptors(): void
    {
        $fake = new FakeWeavedClass();
        $invocation = new ReflectiveMethodInvocation($fake, 'add', [1], [new FakeInterceptor(), new FakeInterceptor()]);
        $invocation->proceed();
        $this->assertSame(1, $fake->a);
    }

    /**
     * Tests that getNamedArguments() returns named parameters
     */
    public function testGetNamedArgumentsReturnsNamedParameters(): void
    {
        $args = $this->invocation->getNamedArguments();
        $this->assertSame((array) $args, ['n' => 1]);
    }

    /**
     * Tests that getNamedArguments() handles default parameter values
     */
    public function testGetNamedArgumentsHandlesDefaultValues(): void
    {
        $invocation = new ReflectiveMethodInvocation(new FakeWeavedClass(), 'defaultValue', [1, null], [new FakeInterceptor(), new FakeInterceptor()]);
        $args = $invocation->getNamedArguments();
        $this->assertSame((array) $args, ['a' => 1, 'b' => null]);
    }

    /**
     * Tests that getAnnotation() returns method annotation
     */
    public function testGetAnnotationReturnsMethodAnnotation(): void
    {
        $fakeMarker = $this->invocation->getMethod()->getAnnotation(FakeMarker::class);
        $this->assertInstanceOf(FakeMarker::class, $fakeMarker);
    }

    /**
     * Tests that getClassAnnotation() returns class annotation
     */
    public function testGetClassAnnotationReturnsClassAnnotation(): void
    {
        $fakeMarker = $this->invocation->getMethod()->getDeclaringClass()->getAnnotation(FakeClassMarker::class);
        $this->assertInstanceOf(FakeClassMarker::class, $fakeMarker);
    }
}
