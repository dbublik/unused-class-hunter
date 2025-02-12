<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Sets;

use DBublik\UnusedClass\Filter\FilterInterface;

abstract readonly class AbstractSet
{
    /**
     * @return iterable<class-string, FilterInterface>
     */
    public function getFilters(): iterable
    {
        return [];
    }

    /**
     * @return iterable<class-string>
     */
    public function getIgnoredClasses(): iterable
    {
        return [];
    }

    /**
     * @return iterable<class-string>
     */
    public function getIgnoredAttributes(): iterable
    {
        return [];
    }
}
