<?php

declare(strict_types=1);

namespace DBublik\UnusedClass;

use DBublik\UnusedClass\Cache\Cache;
use DBublik\UnusedClass\Parser\FileParser;
use DBublik\UnusedClass\ValueObject\FileInformation;
use DBublik\UnusedClass\ValueObject\ParseInformation;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class UnusedClassFinder
{
    private FileParser $fileParser;

    public function __construct(
        private Config $config,
    ) {
        $this->fileParser = new FileParser(new Cache($config->getCacheDir()));
    }

    /**
     * @return FileInformation[]
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
     * @return string[]
     */
    private function getFilePaths(): array
    {
        $filePaths = [];

        foreach ($this->config->getFinder() as $fileInfo) {
            $filePaths[] = (string) $fileInfo->getRealPath();
        }

        return $filePaths;
    }

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
