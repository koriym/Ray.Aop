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
     * Tests Weaver constructor initialization with bindings and compile directory
     *
     * @return Weaver Returns Weaver instance for dependent tests
     */
    public function testWeaverConstructor(): Weaver
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
        $bind = (new Bind())->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');
        $this->assertInstanceOf(Weaver::class, $weaver);

        return $weaver;
    }

    /**
     * Tests weaving a class and verifying the weaved class exists
     *
     * @depends testWeaverConstructor
     */
    public function testWeaveClass(Weaver $weaver): void
    {
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(class_exists($className, false));
    }

    /**
     * Tests loading a weaved class from compiled AOP class file
     *
     * @covers \Ray\Aop\Weaver::loadClass
     * @covers \Ray\Aop\Weaver::weave
     */
    public function testLoadWeavedClass(): void
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), []);
        $bind = (new Bind())->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp_unerase');
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(class_exists($className, false));
    }

    /**
     * Tests creating a new instance of weaved class and verifying interception works
     *
     * @depends testWeaverConstructor
     */
    public function testCreateWeavedInstance(Weaver $weaver): void
    {
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        $this->assertInstanceOf(FakeWeaverMock::class, $weaved);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests serialization and deserialization of Weaver instance
     *
     * @depends testWeaverConstructor
     */
    public function testSerializeDeserializeWeaver(Weaver $weaver): void
    {
        $weaver = unserialize(serialize($weaver));
        $this->assertInstanceOf(Weaver::class, $weaver);
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        $this->assertInstanceOf(FakeWeaverMock::class, $weaved);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * Tests weaving a precompiled class using external script
     */
    public function testWeavePrecompiledClass(): void
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
