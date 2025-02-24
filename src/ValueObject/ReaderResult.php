<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

use DBublik\UnusedClassHunter\Config;

final class ReaderResult
{
    /**
     * @var array<class-string, list<AbstractFileNode>>
     */
    private array $usedFileMap = [];

    /**
     * @var array<class-string, ClassNode>
     */
    private array $usedClasses = [];

    /**
     * @var array<class-string, ClassNode>
     */
    private array $unusedClasses = [];

    /**
     * @param list<AbstractFileNode> $fileNodes
     */
    public function __construct(
        private readonly Config $config,
        array $fileNodes,
    ) {
        $classes = [];

        foreach ($fileNodes as $fileNode) {
            foreach ($fileNode->getUsedClasses() as $usedClass) {
                $this->usedFileMap[$usedClass][] = $fileNode;
            }

            if ($fileNode instanceof ClassNode) {
                $classes[$fileNode->getName()] = $fileNode;
            }
        }

        foreach ($classes as $name => $class) {
            if (isset($this->usedFileMap[$name])) {
                $this->usedClasses[$name] = $class;
            } else {
                $this->unusedClasses[$name] = $class;
            }
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return array<class-string, ClassNode>
     */
    public function getUnusedClasses(): array
    {
        return $this->unusedClasses;
    }

    /**
     * @param class-string $name
     *
     * @return list<AbstractFileNode>
     */
    public function getUsedFilesByName(string $name): array
    {
        return $this->usedFileMap[$name] ?? [];
    }

    /**
     * @param class-string $name
     */
    public function getClassByName(string $name): ?ClassNode
    {
        return $this->usedClasses[$name] ?? $this->unusedClasses[$name] ?? null;
    }
}
