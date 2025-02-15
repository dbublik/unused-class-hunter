<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use DBublik\UnusedClassHunter\Cache\Cache;
use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;

final readonly class FileParser
{
    public function __construct(
        private Cache $cache,
        private ClassParser $parser,
    ) {}

    /**
     * @param non-empty-string $file
     */
    public function parse(string $file): AbstractFileNode
    {
        if (null !== $fileNode = $this->cache->get($file)) {
            return $fileNode;
        }

        $fileNode = $this->parseFile($file);

        $this->cache->set($file, $fileNode);

        return $fileNode;
    }

    /**
     * @param non-empty-string $file
     */
    private function parseFile(string $file): AbstractFileNode
    {
        if (false === $code = file_get_contents($file)) {
            throw new \RuntimeException(\sprintf('Unable to read file %s', $file));
        }

        $parsedFile = $this->parser->parse($code);

        if (null === $name = $parsedFile->getClassName()) {
            return new FileNode(
                file: $file,
                usedClasses: $parsedFile->getUsedClasses(),
            );
        }

        return new ClassNode(
            file: $file,
            usedClasses: $parsedFile->getUsedClasses(),
            name: $name,
            startLine: $parsedFile->getClassStartLine(),
            hasApiTag: $parsedFile->hasClassApiTag(),
            extends: $parsedFile->getExtends(),
            implements: $parsedFile->getImplements(),
            attributes: $parsedFile->getAttributes(),
        );
    }
}
