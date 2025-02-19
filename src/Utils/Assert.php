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
    public static function listOfStrings(array $list): void
    {
        if (!array_is_list($list)) {
            throw new \InvalidArgumentException('Array must have a list of strings');
        }

        foreach ($list as $value) {
            self::nonEmptyString($value);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function nonEmptyString(mixed $value): void
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('Value must be a string, got "%s"', \gettype($value)));
        }
        if ('' === $value) {
            throw new \InvalidArgumentException('Value must be a non empty string');
        }
    }
}
