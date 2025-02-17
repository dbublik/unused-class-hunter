<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final readonly class ClassParser
{
    private function __construct(
        private Parser $parser,
        private ClassNodeTraverser $traverser,
    ) {}

    public static function create(bool $isStrict = false): self
    {
        return new self(
            (new ParserFactory())->createForHostVersion(),
            new ClassNodeTraverser($isStrict),
        );
    }

    /**
     * @throws \RuntimeException
     */
    public function parse(string $code): ParsedFile
    {
        try {
            $nodes = $this->parser->parse($code);
        } catch (Error $error) {
            throw new \RuntimeException(\sprintf('Parse error: %s', $error->getMessage()), $error->getCode(), $error);
        }

        if (null === $nodes) {
            throw new \RuntimeException(\sprintf('Parse error: %s', $code));
        }

        $this->traverser->traverse($nodes);

        return $this->traverser->visitor->getParsedFile();
    }
}
