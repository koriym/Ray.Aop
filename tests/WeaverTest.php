<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

use function class_exists;
use function passthru;
use function serialize;
use function unserialize;

class WeaverTest extends TestCase
{
    /**
     * Tests Weaver class initialization with bindings
     */
    public function testWeaverInitialization(): Weaver
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
        $bind = (new Bind())->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');
        $this->assertInstanceOf(Weaver::class, $weaver);

        return $weaver;
    }

    /**
     * Tests that weave() creates a new AOP proxy class
     *
     * @depends testWeaverInitialization
     */
    public function testWeaveCreatesNewClass(Weaver $weaver): void
    {
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(class_exists($className, false));
    }

    /**
     * Tests that weave() can load compiled AOP classes
     *
     * @covers \Ray\Aop\Weaver::loadClass
     * @covers \Ray\Aop\Weaver::weave
     */
    public function testWeaveLoadsCompiledClass(): void
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), []);
        $bind = (new Bind())->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp_unerase');
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(class_exists($className, false));
    }

    /**
     * Tests creating new instances with interceptors applied
     *
     * @depends testWeaverInitialization
     */
    public function testNewInstanceWithInterceptor(Weaver $weaver): void
    {
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        $this->assertInstanceOf(FakeWeaverMock::class, $weaved);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests that serialized Weaver maintains functionality
     *
     * @depends testWeaverInitialization
     */
    public function testSerializedWeaverMaintainsFunctionality(Weaver $weaver): void
    {
        $weaver = unserialize(serialize($weaver));
        $this->assertInstanceOf(Weaver::class, $weaver);
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        $this->assertInstanceOf(FakeWeaverMock::class, $weaved);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests weaving compiled PHP scripts
     */
    public function testWeaveCompiledScript(): void
    {
        passthru('php ' . __DIR__ . '/script/weave.php');
        $pointcut = new Pointcut(
            (new Matcher())->any(),
            (new Matcher())->any(),
            [new FakeInterceptor()]
        );
        $bind = new Bind();
        $bind->bind(FakeWeaverScript::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');
        $className = $weaver->weave(FakeWeaverScript::class);
        $this->assertTrue(class_exists($className, false));
    }
}
