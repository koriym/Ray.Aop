<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

use function call_user_func_array;

/**
 * @psalm-import-type MethodBindings from Types
 * @psalm-import-type Arguments from Types
 */
trait Php82InterceptTrait
{
    private readonly InterceptTraitState $_state;

    /**
     * @param MethodBindings $bindings
     *
     * @see WeavedInterface::_initState()
     */
    public function _initState(array $bindings): void
    {
        $this->_state = new InterceptTraitState($bindings);
    }

    /**
     * @param Arguments $args
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    private function _intercept(string $func, array $args) // phpcs:ignore
    {
        if (! $this->_state->isAspect) {
            $this->_state->isAspect = true;

            return call_user_func_array([parent::class, $func], $args);
        }

        $this->_state->isAspect = false;
        $result = (new Invocation($this, $func, $args, $this->_state->bindings[$func]))->proceed();
        $this->_state->isAspect = true;

        return $result;
    }
}
