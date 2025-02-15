<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use PhpParser\Parser;
use PhpParser\ParserFactory;

final readonly class ClassParser
{
    private function __construct(
        private Parser $parser,
        private ClassNodeTraverser $traverser,
    ) {}

    public static function create(): self
    {
        return new self(
            (new ParserFactory())->createForHostVersion(),
            new ClassNodeTraverser(),
        );
    }

    public function parse(string $code): ParsedFile
    {
        if (null === $nodes = $this->parser->parse($code)) {
            throw new \RuntimeException(\sprintf('Parse error: %s', $code));
        }

        $this->traverser->traverse($nodes);

        return $this->traverser->visitor->getParsedFile();
    }
}
