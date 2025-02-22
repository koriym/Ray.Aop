<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

use function count;

class ReflectionClassTest extends TestCase
{
    /** @var ReflectionClass<object> */
    private $class;

    public function setUp(): void
    {
        $this->class = new ReflectionClass(FakeClassTartget::class); // @phpstan-ignore-line
    }

    /**
     * Tests that getAnnotations() returns all annotations on the class
     */
    public function testGetClassAnnotations(): void
    {
        $annotations = $this->class->getAnnotations();
        $this->assertSame(2, count($annotations));
    }

    /**
     * Tests that getAnnotation() returns a specific annotation instance
     */
    public function testGetClassAnnotation(): void
    {
        $annotation = $this->class->getAnnotation(FakeResource::class);
        $this->assertInstanceOf(FakeResource::class, $annotation);
    }

    /**
     * Tests that getMethods() returns all methods as ReflectionMethod instances
     */
    public function testGetClassMethods(): void
    {
        $methods = $this->class->getMethods();
        $this->assertAllInstanceOfMethod($methods);
    }

    /**
     * Tests that getConstructor() returns ReflectionMethod for class with constructor
     */
    public function testGetClassConstructor(): void
    {
        $constructor = $this->class->getConstructor();
        $this->assertInstanceOf(ReflectionMethod::class, $constructor);
    }

    /**
     * Tests that getConstructor() returns null for class without constructor
     */
    public function testGetClassConstructorReturnsNullForNoConstructor(): void
    {
        $constructor = (new ReflectionClass(FakeAnnotateClass::class))->getConstructor();
        $this->assertNull($constructor);
    }

    /** @param array<ReflectionMethod> $array */
    private function assertAllInstanceOfMethod(array $array): void
    {
        foreach ($array as $item) {
            $this->assertInstanceOf(ReflectionMethod::class, $item);
        }
    }

    /**
     * Tests that getParentClass() returns ReflectionClass instance for parent class
     */
    public function testGetParentClassReturnsReflectionClass(): void
    {
        $this->assertInstanceOf(ReflectionClass::class, (new ReflectionClass(FakeMockChild::class))->getParentClass());
    }
}
