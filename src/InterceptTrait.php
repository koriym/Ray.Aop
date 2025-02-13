<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

use function call_user_func_array;

/** @psalm-import-type MethodBindings from Types */
trait InterceptTrait
{
    /**
     * @var InterceptTraitState
     * @readonly
     * @internal Public for CompilerTest
     */
    public $state;

    /**
     * @param MethodBindings $bindings
     *
     * @see WeavedInterface::_initState()
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _initState(array $bindings): void // phpcs:ignore
    {
        $this->state = new InterceptTraitState($bindings, true);
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    private function _intercept(string $func, array $args) // phpcs:ignore
    {
        if (! $this->state->isAspect) {
            $this->state->isAspect = true;

            return call_user_func_array([parent::class, $func], $args);
        }

        $this->state->isAspect = false;
        $result = (new Invocation($this, $func, $args, $this->state->bindings[$func]))->proceed();
        $this->state->isAspect = true;

        return $result;
    }
}
