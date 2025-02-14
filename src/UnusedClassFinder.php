<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter;

use DBublik\UnusedClassHunter\Cache\Cache;
use DBublik\UnusedClassHunter\Parser\ClassParser;
use DBublik\UnusedClassHunter\Parser\FileParser;
use DBublik\UnusedClassHunter\ValueObject\FileInformation;
use DBublik\UnusedClassHunter\ValueObject\ParseInformation;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class UnusedClassFinder
{
    private FileParser $fileParser;

    public function __construct(
        private Config $config,
    ) {
        $this->fileParser = new FileParser(
            new Cache($config->getCacheDir()),
            ClassParser::create(),
        );
    }

    /**
     * @return list<FileInformation>
     */
    public function findClasses(SymfonyStyle $io): array
    {
        $filePaths = $this->getFilePaths();

        $information = $this->readFiles($io, $filePaths);

        $result = [];
        foreach ($information->getUnusedClasses() as $unusedClass) {
            if (!$this->isSkipped($unusedClass, $information)) {
                $result[] = $unusedClass;
            }
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private function getFilePaths(): array
    {
        $filePaths = [];

        foreach ($this->config->getFinder() as $fileInfo) {
            $filePaths[] = (string) $fileInfo->getRealPath();
        }

        return $filePaths;
    }

    /**
     * @param list<string> $filePaths
     */
    private function readFiles(SymfonyStyle $io, array $filePaths): ParseInformation
    {
        $files = [];
        $progressBar = $io->createProgressBar(\count($filePaths));

        foreach ($filePaths as $filePath) {
            $files[] = $this->fileParser->parse($filePath);
            $progressBar->advance();
        }

        $io->newLine(2);

        return new ParseInformation($files);
    }

    private function isSkipped(FileInformation $class, ParseInformation $information): bool
    {
        foreach ($this->config->getFilters() as $filter) {
            if ($filter->isIgnored($class, $information, $this->config)) {
                return true;
            }
        }

        return false;
    }
}
