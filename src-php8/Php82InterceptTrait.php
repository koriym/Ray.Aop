<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

use function call_user_func_array;

/**
 * WARNING: All properties in this trait must be readonly to allow its use in a readonly class.
 */
trait Php82InterceptTrait
{
    /**
     * @var InterceptTraitState
     * @internal Public for CompilerTest
     */
    public readonly InterceptTraitState $_state;

    /**
     * @param array<string, array<MethodInterceptor|string>> $bindings
     *
     * @see WeavedInterface::_initState()
     */
    public function _initState(array $bindings): void
    {
        $this->_state = new InterceptTraitState($bindings, true);
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
