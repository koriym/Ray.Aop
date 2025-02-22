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
     * Tests getting class annotations
     */
    public function testGetAnnottaions(): void
    {
        $annotations = $this->class->getAnnotations();
        $this->assertSame(2, count($annotations));
    }

    /**
     * Tests getting a specific class annotation
     */
    public function testGetAnnottaion(): void
    {
        $annotation = $this->class->getAnnotation(FakeResource::class);
        $this->assertInstanceOf(FakeResource::class, $annotation);
    }

    /**
     * Tests getting class methods
     */
    public function testGetMethods(): void
    {
        $methods = $this->class->getMethods();
        $this->assertAllInstanceOfMethod($methods);
    }

    /**
     * Tests getting class constructor
     */
    public function testConstructor(): void
    {
        $constructor = $this->class->getConstructor();
        $this->assertInstanceOf(ReflectionMethod::class, $constructor);
    }

    /**
     * Tests getting constructor for class without constructor
     */
    public function testConstructorNull(): void
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
     * Tests getting parent class
     */
    public function testGetParentClass(): void
    {
        $this->assertInstanceOf(ReflectionClass::class, (new ReflectionClass(FakeMockChild::class))->getParentClass());
    }
}
