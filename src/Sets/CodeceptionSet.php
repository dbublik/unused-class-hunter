<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Sets;

use DBublik\UnusedClass\Filter\CodeceptionTestFilter;
use DBublik\UnusedClass\Filter\FilterInterface;

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
