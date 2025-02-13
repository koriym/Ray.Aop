<?php

declare(strict_types=1);

namespace Ray\Aop;

final class InterceptTraitState
{
    /**
     * @var array<string, array<class-string<MethodInterceptor>>>
     * @readonly
     */
    public $bindings;

    /** @var bool */
    public $isAspect;

    /**
     * @param array<string, array<class-string<MethodInterceptor>>> $bindings
     * @param bool                                                  $isAspect
     */
    public function __construct($bindings, $isAspect)
    {
        $this->bindings = $bindings;
        $this->isAspect = $isAspect;
    }
}
