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
    public static function listOfString(array $list): void
    {
        if (!array_is_list($list)) {
            throw new \InvalidArgumentException('Array must have a list of class');
        }

        foreach ($list as $class) {
            if (!\is_string($class)) {
                throw new \InvalidArgumentException(\sprintf('Class must be a string, got "%s"', \gettype($class)));
            }
        }
    }
}
