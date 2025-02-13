<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

final readonly class PhpunitSet extends AbstractSet
{
    /**
     * @return iterable<class-string>
     */
    public function getIgnoredClasses(): iterable
    {
        return [
            'PHPUnit\Framework\TestCase',
        ];
    }
}
