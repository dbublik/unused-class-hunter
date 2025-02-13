<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\ValueObject;

final readonly class ParseInformation
{
    /**
     * @var list<string>
     */
    private array $usedClassNames;

    /**
     * @var list<FileInformation>
     */
    private array $classes;

    public function __construct(array $files)
    {
        $usedClassNames = [];
        $classes = [];

        /** @var FileInformation $file */
        foreach ($files as $file) {
            $usedClassNames[] = $file->getUsedClassNames();

            if (null !== $file->getClassName()) {
                $classes[] = $file;
            }
        }

        $usedClassNames = array_unique(array_merge(...$usedClassNames));
        sort($usedClassNames);
        $this->usedClassNames = $usedClassNames;

        $this->classes = $classes;
    }

    public function getUsedClassNames(): array
    {
        return $this->usedClassNames;
    }

    public function getClassByClassName(string $className): ?FileInformation
    {
        foreach ($this->classes as $class) {
            if ($className === $class->getClassName()) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @return \Generator<FileInformation>
     */
    public function getUnusedClasses(): \Generator
    {
        $usedClassName = array_flip($this->usedClassNames);

        foreach ($this->classes as $class) {
            if (!isset($usedClassName[$class->getClassName()])) {
                yield $class;
            }
        }
    }
}
