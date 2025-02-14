<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

use function call_user_func_array;

/** @psalm-import-type MethodBindings from Types */
trait InterceptTrait
{
    /**
     * @var MethodBindings
     * @readonly
     */
    public $bindings = [];

    /**
     * @var bool
     */
    private $_isAspect = true;

    /**
     * @param MethodBindings $bindings
     *
     * @see WeavedInterface::_initState()
     */
    public function _initState(array $bindings): void
    {
        $this->bindings = $bindings;
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
        if (! $this->_isAspect) {
            $this->_isAspect = true;

            return call_user_func_array([parent::class, $func], $args);
        }

        $this->_isAspect = false;
        $result = (new Invocation($this, $func, $args, $this->bindings[$func]))->proceed();
        $this->_isAspect = true;

        return $result;
    }
}
