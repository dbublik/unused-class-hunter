<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

use DBublik\UnusedClassHunter\Config;

final readonly class ReaderResult
{
    /**
     * @var array<class-string>
     */
    private array $usedClasses;

    /**
     * @var array<class-string, ClassNode>
     */
    private array $classes;

    /**
     * @param list<AbstractFileNode> $fileNodes
     */
    public function __construct(
        private Config $config,
        array $fileNodes,
    ) {
        $usedClasses = [];
        $classes = [];

        foreach ($fileNodes as $fileNode) {
            $usedClasses[] = $fileNode->getUsedClasses();

            if ($fileNode instanceof ClassNode) {
                $classes[$fileNode->getName()] = $fileNode;
            }
        }

        $this->usedClasses = array_merge(...$usedClasses);
        $this->classes = $classes;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param class-string $name
     */
    public function getClassByName(string $name): ?ClassNode
    {
        return $this->classes[$name] ?? null;
    }

    /**
     * @return \Generator<ClassNode>
     */
    public function getUnusedClasses(): \Generator
    {
        $usedClasses = array_flip($this->usedClasses);

        foreach ($this->classes as $name => $class) {
            if (!isset($usedClasses[$name])) {
                yield $class;
            }
        }
    }
}
