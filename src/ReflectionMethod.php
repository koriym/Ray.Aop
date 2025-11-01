<?php

declare(strict_types=1);

namespace Ray\Aop;

use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\AttributeReaderInterface;
use Override;

use function assert;
use function class_exists;
use function is_object;

final class ReflectionMethod extends \ReflectionMethod implements Reader
{
    /** @var ?WeavedInterface */
    private $object;

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
     * Set dependencies
     */
    public function setObject(WeavedInterface $object): void
    {
        $this->object = $object;
    }

    /**
     * @return ReflectionClass<object>
     *
     * @psalm-external-mutation-free
     * @psalm-suppress MethodSignatureMismatch
     */
    #[Override]
    public function getDeclaringClass(): ReflectionClass
    {
        if (! is_object($this->object)) {
            return new ReflectionClass($this->class);
        }

        $parencClass = (new \ReflectionClass($this->object))->getParentClass();
        assert($parencClass instanceof \ReflectionClass);
        $originalClass = $parencClass->name;

        return new ReflectionClass($originalClass);
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
        assert(class_exists($this->class));
        /** @var list<object> $annotations */
        $annotations = self::getReader()->getMethodAttributes(new \ReflectionMethod($this->class, $this->name));

        return $annotations;
    }

    /**
     * @param class-string<T> $annotationName
     *
     * @return T|null
     *
     * @template T of object
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
}
