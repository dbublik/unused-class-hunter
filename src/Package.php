<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter;

final readonly class Package
{
    public const string NAME = 'dbublik/unused-class-hunter';
    public const string VERSION = '1.0.0';

    /**
     * @return non-empty-string
     */
    public static function getVersion(): string
    {
        return self::VERSION . self::getComposerVersion();
    }

    /**
     * @return null|non-empty-string
     */
    public static function getComposerVersion(): ?string
    {
        return (new ComposerResolver())->getVersion(self::NAME);
    }
}
