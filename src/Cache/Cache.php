<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Cache;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Package;
use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;
use Symfony\Component\Finder\Finder;

final class Cache implements CacheInterface
{
    private readonly FileHandler $rootCacheFile;
    private bool $signatureWasUpdated = true;

    /** @var list<string> */
    private readonly array $oldFiles;

    /** @var list<string> */
    private array $newFiles = [];

    private function __construct(
        private readonly string $cacheDir,
        private readonly Signature $signature,
    ) {
        $this->rootCacheFile = new FileHandler($this->cacheDir . '/.hunter.cache');
        $this->getRootCache();
        $this->oldFiles = $this->getOldFiles();
    }

    public function __destruct()
    {
        foreach (array_diff($this->oldFiles, $this->newFiles) as $oldFile) {
            unlink($oldFile);
        }
    }

    public static function create(Config $config): self
    {
        return new self(
            cacheDir: $config->getCacheDir(),
            signature: new Signature(
                phpVersion: PHP_VERSION,
                packageVersion: Package::getVersion(),
                config: [
                    'isStrictMode' => $config->isStrictMode(),
                    'bootstrapFiles' => $config->getBootstrapFiles(),
                ],
            ),
        );
    }

    /**
     * @param non-empty-string $file
     */
    #[\Override]
    public function get(string $file): ?AbstractFileNode
    {
        $cacheFile = $this->getFile($file);
        $this->newFiles[] = $cacheFile->getName();

        if ($this->signatureWasUpdated) {
            return null;
        }

        if (null === $data = $cacheFile->read()) {
            return null;
        }

        if (\array_key_exists('class', $data)) {
            return ClassNode::fromData($data);
        }

        return FileNode::fromData($data);
    }

    /**
     * @param non-empty-string $file
     */
    #[\Override]
    public function set(string $file, AbstractFileNode $fileNode): void
    {
        $this->getFile($file)->write($fileNode);
    }

    /**
     * @param non-empty-string $file
     */
    private function getFile(string $file): FileHandler
    {
        return new FileHandler($this->cacheDir . '/classes/' . hash_file('sha256', $file));
    }

    private function getRootCache(): void
    {
        if (null !== $data = $this->rootCacheFile->read()) {
            $oldSignature = Signature::fromData($data);

            if (null !== $oldSignature && $this->signature->equals($oldSignature)) {
                $this->signatureWasUpdated = false;

                return;
            }
        }

        $this->rootCacheFile->write($this->signature);
    }

    /**
     * @return list<string>
     */
    private function getOldFiles(): array
    {
        if (!file_exists($directory = $this->cacheDir . '/classes')) {
            return [];
        }

        $files = [];
        foreach (Finder::create()->in($directory)->files() as $file) {
            if (false !== $path = $file->getRealPath()) {
                $files[] = $path;
            }
        }

        return $files;
    }
}
