<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

use DBublik\UnusedClassHunter\Utils\Assert;

abstract readonly class AbstractFileNode implements \JsonSerializable
{
    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(
        /** @var non-empty-string */
        private string $file,
        /** @var list<class-string> */
        private array $usedClasses,
    ) {
        Assert::listOfString($this->usedClasses);
    }

    /**
     * @return non-empty-string
     */
    final public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return list<class-string>
     */
    final public function getUsedClasses(): array
    {
        return $this->usedClasses;
    }
}
