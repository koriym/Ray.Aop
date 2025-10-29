<?php

declare(strict_types=1);

namespace Ray\ServiceLocator;

use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\AttributeReaderInterface;

/**
 * ServiceLocator class provides a way to set and retrieve a Reader instance.
 * It includes mechanisms to lazily initialize the Reader if it hasn't been set.
 *
 * @deprecated Use AttributeReaderInterface directly instead.
 */
final class ServiceLocator
{
    /** @var ?AttributeReaderInterface */
    private static $reader;

    public static function setReader(AttributeReaderInterface $reader): void
    {
        self::$reader = $reader;
    }

    public static function getReader(): AttributeReaderInterface
    {
        if (! self::$reader) {
            self::$reader = new AttributeReader();
        }

        return self::$reader;
    }
}
