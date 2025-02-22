<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class NullInterceptorTest extends TestCase
{
    /**
     * Tests that NullInterceptor passes through the original method invocation without modification
     */
    public function testInvokeWithNullInterceptor(): void
    {
        $invocation = new ReflectiveMethodInvocation(new FakeMock(), 'returnSame', [1]);
        $result = (new NullInterceptor())->invoke($invocation);
        $this->assertSame(1, $result);
    }
}
