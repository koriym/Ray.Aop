<?php

declare(strict_types=1);

namespace Ray\Aop;

final class NullInterceptor implements MethodInterceptor
{
    /** @return mixed */
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
