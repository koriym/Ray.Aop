<?php

declare(strict_types=1);

namespace Ray\Aop;

use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\AttributeReaderInterface;
use Override;
use ReturnTypeWillChange;

use function get_class_methods;

/**
 * @template T of object
 * @template-extends \ReflectionClass<T>
 */
final class ReflectionClass extends \ReflectionClass implements Reader
{
    private static ?AttributeReaderInterface $reader = null;

    public static function setReader(AttributeReaderInterface $reader): void
    {
        self::$reader = $reader;
    }

    private static function getReader(): AttributeReaderInterface
    {
        if (self::$reader === null) {
            self::$reader = new AttributeReader();
        }

        return self::$reader;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress DeprecatedClass
     */
    #[Override]
    public function getAnnotations(): array
    {
        /** @var list<object> $annotations */
        $annotations = self::getReader()->getClassAttributes(new \ReflectionClass($this->name));

        return $annotations;
    }

    /**
     * @param class-string<TAnnotation> $annotationName
     *
     * @return TAnnotation|null
     *
     * @template TAnnotation of object
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     * @psalm-external-mutation-free
     */
    #[Override]
    public function getAnnotation(string $annotationName)
    {
        $annotations = $this->getAnnotations();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param int|null $filter
     *
     * @return list<ReflectionMethod>
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function getMethods($filter = null): array
    {
        unset($filter);
        $methods = [];
        $methodNames = get_class_methods($this->name);
        foreach ($methodNames as $methodName) {
            $methods[] = new ReflectionMethod($this->name, $methodName);
        }

        return $methods;
    }

    /**
     * @psalm-suppress MethodSignatureMismatch
     * @psalm-external-mutation-free
     */
    #[Override]
    public function getConstructor(): ?\ReflectionMethod
    {
        $parent = parent::getConstructor();
        if ($parent === null) {
            return null;
        }

        return new ReflectionMethod($parent->class, $parent->name);
    }

    /**
     * @return ReflectionClass<object>|false
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    #[ReturnTypeWillChange]
    public function getParentClass()
    {
        $parent = \ReflectionClass::getParentClass();

        return $parent instanceof \ReflectionClass ? (new ReflectionClass($parent->getName())) : false;
    }
}
