<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Filter\CodeceptionTestFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;

final readonly class CodeceptionSet extends AbstractSet
{
    /**
     * @return iterable<class-string, FilterInterface>
     */
    public function getFilters(): iterable
    {
        return [
            new CodeceptionTestFilter(),
        ];
    }

    /**
     * @return iterable<class-string>
     */
    public function getIgnoredClasses(): iterable
    {
        return [
            'Codeception\Module',
        ];
    }
}
