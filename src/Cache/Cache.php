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
    private readonly string $cacheDir;
    private readonly FileHandler $rootCacheFile;
    private bool $signatureWasUpdated = true;

    /** @var list<string> */
    private readonly array $oldFiles;

    /** @var list<string> */
    private array $newFiles = [];

    private function __construct(
        string $cacheDir,
        private readonly Signature $signature,
    ) {
        $this->cacheDir = $cacheDir . '/classes';
        $this->rootCacheFile = new FileHandler($cacheDir . '/.hunter.cache');
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
        if ($this->signatureWasUpdated) {
            return null;
        }

        $cacheFile = $this->getFile($file);

        if (null === $data = $cacheFile->read()) {
            return null;
        }

        if (\array_key_exists('class', $data)) {
            $node = ClassNode::fromData($data);
        } else {
            $node = FileNode::fromData($data);
        }

        if (null === $node) {
            return null;
        }

        $this->newFiles[] = $cacheFile->getName();

        return $node;
    }

    #[\Override]
    public function set(AbstractFileNode $fileNode): void
    {
        $cacheFile = $this->getFile($fileNode->getFile());
        $cacheFile->write($fileNode);

        $this->newFiles[] = $cacheFile->getName();
    }

    /**
     * @param non-empty-string $file
     */
    private function getFile(string $file): FileHandler
    {
        return new FileHandler($this->cacheDir . '/' . hash_file('sha256', $file));
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
        if (!file_exists($directory = $this->cacheDir)) {
            return [];
        }

        $files = [];
        foreach (Finder::create()->in($directory)->files() as $file) {
            $files[] = $file->getPathname();
        }

        return $files;
    }
}
