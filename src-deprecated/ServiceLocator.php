<?php

declare(strict_types=1);

namespace Ray\ServiceLocator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;

use function class_exists;
use function trigger_error;
use function vsprintf;

/**
 * ServiceLocator class provides a way to set and retrieve a Reader instance.
 * It includes mechanisms to lazily initialize the Reader if it hasn't been set.
 *
 * @deprecated Use AttributeReaderInterface directly instead.
 */
final class ServiceLocator
{
    /** @var ?Reader */
    private static $reader;

    public static function setReader(Reader $reader): void
    {
        trigger_error('ray/aop does not use this class. The call code can be deleted.', E_USER_DEPRECATED);
        self::$reader = $reader;
    }

    public static function getReader(): Reader
    {
        if (! class_exists(AttributeReader::class)) {
            trigger_error(vsprintf('Please install "koriym/attributes" to use %s', [self::class]), E_USER_DEPRECATED);
        }
        if (! self::$reader) {
            self::$reader = new DualReader(new AnnotationReader(), new AttributeReader());
        }

        return self::$reader;
    }
}
