<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeGlobalEmptyNamespaced;
use FakeGlobalNamespaced;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\Annotation\FakeMarker3;
use Ray\Aop\Exception\NotWritableException;
use ReflectionClass;
use ReflectionMethod;

use function array_shift;
use function assert;
use function class_exists;
use function file_get_contents;
use function is_array;
use function passthru;
use function serialize;
use function unserialize;

class CompilerTest extends TestCase
{
    /** @var BindInterface */
    private $bind;

    /** @var Compiler */
    private $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new Compiler(__DIR__ . '/tmp');
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
        $this->bind = (new Bind())->bind(FakeWeaved::class, [$pointcut]);
    }

    /**
     * Tests creating a new instance of weaved class
     *
     * @return FakeMock Returns weaved instance for dependent tests
     */
    public function testCreateWeavedInstance(): FakeMock
    {
        $mock = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $this->assertInstanceOf(FakeMock::class, $mock);

        return $mock;
    }

    /**
     * Tests creating multiple instances from the same compiled weaved class
     */
    public function testCreateMultipleWeavedInstances(): void
    {
        $class1 = $this->compiler->compile(FakeMock::class, $this->bind);
        $class2 = $this->compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class1));
        $this->assertSame($class1, $class2);
        $class1File = (new ReflectionClass($class1))->getFileName();
        $class2File = (new ReflectionClass($class1))->getFileName();
        $this->assertSame($class1File, $class2File);
    }

    /**
     * Tests that weaved class has correct parent class
     *
     * @depends testCreateWeavedInstance
     */
    public function testVerifyWeavedClassParent(object $class): void
    {
        $parent = (new ReflectionClass($class))->getParentClass();
        if (! ($parent instanceof ReflectionClass)) {
            return;
        }

        $this->assertSame(FakeMock::class, $parent->getName());
    }

    /**
     * Tests functionality of weaved class including method interception
     *
     * @depends testCreateWeavedInstance
     */
    public function testVerifyWeavedClassFunctionality(FakeMock $weaved): void
    {
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests parent class relationship of weaved instance
     *
     * @return FakeMock Returns weaved instance for dependent tests
     */
    public function testVerifyWeavedClassParentClass(): FakeMock
    {
        $weaved = $this->testCreateWeavedInstance();
        $parent = (new ReflectionClass($weaved))->getParentClass();
        if ($parent instanceof ReflectionClass) {
            $this->assertSame(FakeMock::class, $parent->getName());
        }

        return $weaved;
    }

    /**
     * Tests interceptor functionality in weaved class across multiple calls
     *
     * @depends testCreateWeavedInstance
     */
    public function testVerifyWeavedClassInterceptor(FakeMock $weaved): void
    {
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests method return value in weaved class
     *
     * @depends testCreateWeavedInstance
     */
    public function testMethodReturnValue(FakeMock $weaved): void
    {
        $num = new FakeNum();
        $num->value = 1;
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests method interception in parent class
     */
    public function testParentMethodIntercept(): void
    {
        $mock = $this->compiler->newInstance(FakeMockGrandChild::class, [], $this->bind);
        $result = $mock->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests method interception with typed parameters
     */
    public function testTypedParentMethodIntercept(): void
    {
        $bind = (new Bind())->bindInterceptors('passIterator', [new NullInterceptor()]);
        $mock = $this->compiler->newInstance(FakeTypedMockGrandChild::class, [], $bind);
        $result = $mock->passIterator(new ArrayIterator());
        $this->assertInstanceOf(ArrayIterator::class, $result);
    }

    /**
     * Tests method interception in grandparent class
     */
    public function testParentOfParentMethodIntercept(): void
    {
        $mock = $this->compiler->newInstance(FakeMockChildChild::class, [], $this->bind);
        $result = $mock->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests accessing private values in weaved class
     */
    public function testGetPrivateVal(): void
    {
        $mock = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $val = $mock->getPrivateVal();
        $this->assertSame(1, $val);
    }

    /**
     * Tests abort proceed interceptor called multiple times
     */
    public function testCallAbortProceedInterceptorTwice(): void
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeAbortProceedInterceptor()]);
        $this->bind->bind(FakeWeaved::class, [$pointcut]);
        $mock = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $this->assertSame(40, $mock->returnSame(1));
        $this->assertSame(40, $mock->returnSame(1));
    }

    /**
     * Tests preservation of class doc comments
     */
    public function testClassDocComment(): void
    {
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        /** @var FakeMock $weaved */
        $docComment = (string) (new ReflectionClass($weaved))->getDocComment();
        $expected = (new ReflectionClass(FakeMock::class))->getDocComment();
        $this->assertStringContainsString('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    /**
     * Tests preservation of method doc comments
     */
    public function testMethodDocComment(): void
    {
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        /** @var FakeMock $weaved */
        $docComment = (string) (new ReflectionClass($weaved))->getMethods()[0]->getDocComment();
        $expected = (new ReflectionClass(FakeMock::class))->getMethods()[0]->getDocComment();

        $this->assertStringContainsString('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    /**
     * Tests handling of classes and methods without doc comments
     */
    public function testNoDocComment(): void
    {
        $weaved = $this->compiler->newInstance(FakeMockNoDoc::class, [], $this->bind);
        /** @var FakeMock $weaved */
        $classDocComment = (new ReflectionClass($weaved))->getDocComment();
        $methodDocComment = (new ReflectionClass($weaved))->getMethods()[0]->getDocComment();

        $this->assertFalse((bool) $classDocComment);
        $this->assertFalse((bool) $methodDocComment);
    }

    /**
     * Tests serialization and deserialization of compiler
     */
    public function testSerialize(): void
    {
        $compiler = unserialize(serialize($this->compiler));
        $this->assertInstanceOf(Compiler::class, $compiler);
        $class = $compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class));
    }

    /**
     * Tests including compiled file
     */
    public function testIncludeCompilerFile(): void
    {
        passthru('php ' . __DIR__ . '/script/compile.php');
        /** @var class-string $mock */
        $mock = require __DIR__ . '/script/compile.php';
        $isWeaved = (new ReflectionClass($mock))->implementsInterface(WeavedInterface::class);
        $this->assertTrue($isWeaved);
    }

    /**
     * Tests compilation without bindings
     */
    public function testCompileNoBInd(): void
    {
        $class = $this->compiler->compile(FakeMock::class, new Bind());
        $this->assertSame(FakeMock::class, $class);
    }

    /**
     * Tests handling of method annotations
     */
    public function testAnnotation(): void
    {
        $class = $this->compiler->compile(FakeAnnotateClass::class, $this->bind);
        /** @var object[] $annotations */
        $annotations = (new AnnotationReader())->getMethodAnnotations(new ReflectionMethod($class, 'getDouble'));
        $this->assertCount(4, $annotations);
    }

    /**
     * Tests handling of classes without namespace
     */
    public function testNoNamespace(): void
    {
        $class = $this->compiler->compile(FakeAnnotateClassNoName::class, $this->bind);
        /** @var object[] $annotations */
        $annotations = (new AnnotationReader())->getMethodAnnotations(new ReflectionMethod($class, 'getDouble'));
        $this->assertCount(3, $annotations);
    }

    /**
     * Tests handling of array typehints and callables
     */
    public function testArrayTypehintedAndCallable(): void
    {
        $class = $this->compiler->compile(FakeArrayTypehinted::class, $this->bind);
        $this->assertTrue(class_exists($class));
        $file = (string) file_get_contents((string) (new ReflectionClass($class))->getFileName());
        $expected = 'public function returnSame(array $arrayParam, callable $callableParam)';
        $this->assertStringContainsString($expected, $file);
    }

    /**
     * Tests exception when directory is not writable
     */
    public function testNotWritable(): void
    {
        $this->expectException(NotWritableException::class);

        new Compiler('./not_available');
    }

    /**
     * Tests compilation with bindings
     */
    public function testHasBound(): void
    {
        $this->compiler = new Compiler(__DIR__ . '/tmp');
        $this->bind = new Bind();
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
        $this->bind->bind(FakeMock::class, [$pointcut]);
        $class = $this->compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class));
    }

    /**
     * Tests method annotation reader functionality
     */
    public function testMethodAnnotationReader(): void
    {
        $bind = (new Bind())->bindInterceptors('getDouble', [new FakeMethodAnnotationReaderInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakeAnnotateClass::class, [], $bind);
        $this->assertInstanceOf(FakeAnnotateClass::class, $mock);
        $mock->getDouble(1);
        $methodAnnotation = FakeMethodAnnotationReaderInterceptor::$methodAnnotation;
        $this->assertInstanceOf(FakeMarker::class, $methodAnnotation);
        /** @var object[] $methodAnnotations */
        $methodAnnotations = FakeMethodAnnotationReaderInterceptor::$methodAnnotations;
        $this->assertCount(4, $methodAnnotations);
        $annotation = array_shift($methodAnnotations);
        $this->assertInstanceOf(FakeMarker3::class, $annotation);
    }

    /**
     * Tests class annotation reader functionality
     *
     * @depends testMethodAnnotationReader
     */
    public function testClassAnnotationReader(): void
    {
        $classAnnotation = FakeMethodAnnotationReaderInterceptor::$classAnnotation;
        $classAnnotations = FakeMethodAnnotationReaderInterceptor::$classAnnotations;
        $this->assertInstanceOf(FakeClassAnnotation::class, $classAnnotation);
        /** @var object[] $classAnnotations */
        $this->assertCount(2, $classAnnotations);
        $annotation = array_shift($classAnnotations);
        $this->assertInstanceOf(FakeResource::class, $annotation);
    }

    /**
     * Tests method annotation reader with no annotations
     */
    public function testMethodAnnotationReaderReturnNull(): void
    {
        $bind = (new Bind())->bindInterceptors('returnSame', [new FakeMethodAnnotationReaderInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakeMock::class, [], $bind);
        $this->assertInstanceOf(FakeMock::class, $mock);
        $mock->returnSame(1);
        $this->assertNull(FakeMethodAnnotationReaderInterceptor::$methodAnnotation);
        assert(is_array(FakeMethodAnnotationReaderInterceptor::$methodAnnotations));
        $this->assertCount(0, FakeMethodAnnotationReaderInterceptor::$methodAnnotations);
    }

    /**
     * Tests interceptor's ability to change method arguments
     */
    public function testInterceptorCanChangeArgument(): void
    {
        $bind = (new Bind())->bindInterceptors('returnSame', [new FakeChangeArgsInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakeMock::class, [], $bind);
        $this->assertInstanceOf(FakeMock::class, $mock);
        $mock->returnSame(1);
        $this->assertSame('changed', $mock->returnSame(1));
    }

    /**
     * Tests handling of unnamespaced classes
     */
    public function testUnnamespacedClass(): void
    {
        $mock = $this->compiler->newInstance(FakeGlobalNamespaced::class, [], $this->bind);
        $this->assertInstanceOf(FakeGlobalNamespaced::class, $mock);
        $this->assertSame(2, $mock->returnSame(1));
    }

    /**
     * Tests handling of classes with empty namespace
     */
    public function testEmptyNamespaceClass(): void
    {
        $mock = $this->compiler->newInstance(FakeGlobalEmptyNamespaced::class, [], $this->bind);
        $this->assertInstanceOf(FakeGlobalEmptyNamespaced::class, $mock);
        $this->assertSame(2, $mock->returnSame(1));
    }

    /**
     * Tests handling of void return type methods
     */
    public function testVoidFunction(): void
    {
        $bind = (new Bind())->bindInterceptors('returnTypeVoid', [new FakeChangeArgsInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakePhp71NullableClass::class, [], $bind);
        $this->assertInstanceOf(FakePhp71NullableClass::class, $mock);
        $mock->returnTypeVoid();
        $this->assertTrue($mock->returnTypeVoidCalled);
    }

    /**
     * Tests creating instance with anonymous class
     */
    public function testNewInstanceWithAnonymousClass(): void
    {
        $mock = $this->compiler->newInstance(FakeAnonymousClass::class, [], $this->bind);
        $this->assertInstanceOf(FakeAnonymousClass::class, $mock);
        $this->assertInstanceOf(WeavedInterface::class, $mock);
    }

    /**
     * Tests handling of methods with mixed arguments
     *
     * @requires PHP 8.0
     */
    public function testMethodWithMixedArgument(): void
    {
        $mock = $this->compiler->newInstance(FakeMixedParamClass::class, [], $this->bind);
        $this->assertInstanceOf(FakeMixedParamClass::class, $mock);
        $this->assertInstanceOf(WeavedInterface::class, $mock);
    }

    /**
     * Tests creating instance with PHP 8.2 readonly class
     *
     * @requires PHP 8.2
     */
    public function testNewInstanceWithPhp82ReadOnlyClass(): void
    {
        $mock = $this->compiler->newInstance(FakePhp82ReadOnlyClass::class, [], $this->bind);
        $this->assertInstanceOf(FakePhp82ReadOnlyClass::class, $mock);
        $this->assertInstanceOf(WeavedInterface::class, $mock);
    }
}
