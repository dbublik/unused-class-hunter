<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter;

/**
 * @infection-ignore-all
 */
final readonly class Package
{
    public const NAME = 'dbublik/unused-class-hunter';
    public const VERSION = '1.3.0';

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
