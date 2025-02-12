<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Sets;

final readonly class DoctrineSet extends AbstractSet
{
    /**
     * @return iterable<class-string>
     */
    public function getIgnoredAttributes(): iterable
    {
        return [
            'Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener',
        ];
    }
}
