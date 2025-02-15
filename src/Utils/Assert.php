<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Utils;

final readonly class Assert
{
    /**
     * @throws \InvalidArgumentException
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public static function listOfClassString(array $list): void
    {
        if (!array_is_list($list)) {
            throw new \InvalidArgumentException('Array must have a list of class');
        }

        foreach ($list as $class) {
            if (!\is_string($class)) {
                throw new \InvalidArgumentException(\sprintf('Class must be a string, got "%s"', \gettype($class)));
            }

            self::classExists($class);
        }
    }

    public static function classExists(string $class): void
    {
        if (!class_exists($class) && !interface_exists($class) && !trait_exists($class) && !enum_exists($class)) {
            throw new \InvalidArgumentException(\sprintf('Class "%s" does not exist', $class));
        }
    }
}
