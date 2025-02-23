<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Cache;

use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;

interface CacheInterface
{
    /**
     * @param non-empty-string $file
     */
    public function get(string $file): ?AbstractFileNode;

    public function set(AbstractFileNode $fileNode): void;
}
