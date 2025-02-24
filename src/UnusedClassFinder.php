<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter;

use DBublik\UnusedClassHunter\Cache\Cache;
use DBublik\UnusedClassHunter\Parser\ClassParser;
use DBublik\UnusedClassHunter\Parser\FileParser;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class UnusedClassFinder
{
    private FileParser $fileParser;

    public function __construct(
        private Config $config,
    ) {
        $this->fileParser = new FileParser(
            parser: ClassParser::create($config->isStrictMode()),
            cache: Cache::create($config),
        );
    }

    /**
     * @return list<ClassNode>
     */
    public function findClasses(SymfonyStyle $io): array
    {
        $files = $this->getFiles();
        $readerResult = $this->readFiles($io, $files);

        $classes = [];
        foreach ($readerResult->getUnusedClasses() as $class) {
            if (!$this->isSkipped($class, $readerResult)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    public function deleteClasses(ClassNode ...$classes): void
    {
        foreach ($classes as $class) {
            unlink($class->getFile());
        }
    }

    /**
     * @return list<non-empty-string>
     */
    private function getFiles(): array
    {
        $files = [];

        foreach ($this->config->getFinder() as $fileInfo) {
            /** @var false|non-empty-string $file */
            if (false !== $file = $fileInfo->getRealPath()) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @param list<non-empty-string> $files
     */
    private function readFiles(SymfonyStyle $io, array $files): ReaderResult
    {
        $fileNodes = [];
        $progressBar = $io->createProgressBar(\count($files));

        foreach ($files as $filePath) {
            $fileNodes[] = $this->fileParser->parse($filePath);
            $progressBar->advance();
        }

        $io->newLine(2);

        return new ReaderResult($this->config, $fileNodes);
    }

    private function isSkipped(ClassNode $class, ReaderResult $reader): bool
    {
        foreach ($this->config->getFilters() as $filter) {
            if ($filter->isIgnored($class, $reader)) {
                return true;
            }
        }

        return false;
    }
}
