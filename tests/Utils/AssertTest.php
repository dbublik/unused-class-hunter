<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Utils;

use DBublik\UnusedClassHunter\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Assert::class)]
final class AssertTest extends TestCase
{
    /**
     * @param list<string> $list
     */
    #[DataProvider('provideListOfStrings')]
    public function testListOfString(array $list): void
    {
        $exception = null;

        try {
            Assert::listOfStrings($list);
        } catch (\InvalidArgumentException $e) {
            $exception = $e;
        }

        self::assertNull($exception);
    }

    /**
     * @return iterable<array{0: list<string>}>
     */
    public static function provideListOfStrings(): iterable
    {
        yield [
            [],
        ];

        yield [
            ['value'],
        ];

        yield [
            ['value', 'next_value', 'other_value'],
        ];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideListOfStringsException')]
    public function testListOfStringException(string $exceptionMessage, array $list): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        Assert::listOfStrings($list);
    }

    /**
     * @return iterable<array{0: string, 1: array}>
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public static function provideListOfStringsException(): iterable
    {
        yield [
            'Array must have a list of strings',
            ['key' => 'value'],
        ];

        yield [
            'Array must have a list of strings',
            [0 => 'value', 2 => 'value'],
        ];

        yield [
            'Value must be a string, got "integer"',
            [1],
        ];
    }

    #[DataProvider('provideNonEmptyString')]
    public function testNonEmptyString(string $value): void
    {
        $exception = null;

        try {
            Assert::nonEmptyString($value);
        } catch (\InvalidArgumentException $e) {
            $exception = $e;
        }

        self::assertNull($exception);
    }

    /**
     * @return iterable<array{0: string}>
     */
    public static function provideNonEmptyString(): iterable
    {
        yield [
            'value',
        ];

        yield [
            '0',
        ];

        yield [
            ' ',
        ];
    }

    #[DataProvider('provideNonEmptyStringException')]
    public function testNonEmptyStringException(string $exceptionMessage, mixed $value): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        Assert::nonEmptyString($value);
    }

    /**
     * @return iterable<array{0: string, 1: mixed}>
     */
    public static function provideNonEmptyStringException(): iterable
    {
        yield [
            'Value must be a string, got "integer"',
            1,
        ];

        yield [
            'Value must be a string, got "array"',
            [],
        ];

        yield [
            'Value must be a string, got "object"',
            new \stdClass(),
        ];

        yield [
            'Value must be a non empty string',
            '',
        ];
    }
}
