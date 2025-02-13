<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use DBublik\UnusedClassHunter\Cache\Cache;
use DBublik\UnusedClassHunter\ValueObject\FileInformation;

final readonly class FileParser
{
    public function __construct(
        private Cache $cache,
        private ClassParser $parser = new ClassParser(),
    ) {}

    public function parse(string $filePath): FileInformation
    {
        if (null !== $information = $this->cache->get($filePath)) {
            return $information;
        }

        $information = $this->parseFile($filePath);

        $this->cache->set($filePath, $information);

        return $information;
    }

    private function parseFile(string $filePath): FileInformation
    {
        $code = file_get_contents($filePath);

        $file = $this->parser->parse($code);
        $file->setFile($filePath);

        return $file;
    }
}
