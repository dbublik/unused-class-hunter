<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

final readonly class TwigSet extends AbstractSet
{
    /**
     * @return iterable<class-string>
     */
    public function getIgnoredClasses(): iterable
    {
        return [
            'Twig\Extension\ExtensionInterface',
        ];
    }
}
