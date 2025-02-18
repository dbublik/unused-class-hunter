<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Filter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\ApiTagFilter;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ApiTagFilter::class)]
final class ApiTagFilterTest extends TestCase
{
    #[DataProvider('provideHasApiTag')]
    public function testIsIgnored(bool $hasApiTag): void
    {
        $filter = new ApiTagFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            hasApiTag: $hasApiTag,
        );
        $readerResult = new ReaderResult(new Config(), []);

        $isIgnored = $filter->isIgnored($classNode, $readerResult);

        self::assertSame($hasApiTag, $isIgnored);
    }

    /**
     * @return iterable<array{0: bool}>
     */
    public static function provideHasApiTag(): iterable
    {
        yield [false];

        yield [true];
    }
}
