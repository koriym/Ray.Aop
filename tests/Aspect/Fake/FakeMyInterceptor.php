<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeMyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation): string
    {
        // Pre-processing logic
        $result = $invocation->proceed();
        assert(is_string($result));

        // Post-processing logic
        return 'intercepted ' . $result;
    }
}
