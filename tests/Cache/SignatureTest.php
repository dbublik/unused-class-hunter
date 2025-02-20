<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Cache;

use DBublik\UnusedClassHunter\Cache\Signature;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Signature::class)]
final class SignatureTest extends TestCase
{
    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('provideFromData')]
    public function testFromData(bool $isValid, array $data): void
    {
        $signature = Signature::fromData($data);

        self::assertSame($isValid, null !== $signature);
    }

    /**
     * @return iterable<array{0: bool, 1: array<string, mixed>}>
     */
    public static function provideFromData(): iterable
    {
        yield [
            false,
            [],
        ];

        yield [
            false,
            [
                'phpVersion' => '',
                'packageVersion' => '',
                'config' => '',
            ],
        ];

        yield [
            false,
            [
                'phpVersion' => 1,
                'packageVersion' => 1,
                'config' => [],
            ],
        ];

        yield [
            false,
            [
                'phpVersion' => '1.0',
                'packageVersion' => '1.0',
                'config' => [],
            ],
        ];

        yield [
            true,
            [
                'phpVersion' => '1.0',
                'packageVersion' => '1.0',
                'config' => ['key' => 'value'],
            ],
        ];
    }

    /**
     * @param non-empty-string $phpVersion
     * @param non-empty-string $packageVersion
     * @param non-empty-array<string, mixed> $config
     */
    #[DataProvider('provideJsonSerialize')]
    public function testJsonSerialize(
        string $phpVersion,
        string $packageVersion,
        array $config,
        string $expectedJson
    ): void {
        $signature = new Signature($phpVersion, $packageVersion, $config);

        $json = json_encode($signature, JSON_THROW_ON_ERROR);

        self::assertSame($expectedJson, $json);
    }

    /**
     * @return iterable<array{0: non-empty-string, 1: non-empty-string, 2: non-empty-array<string, mixed>, 3: string}>
     */
    public static function provideJsonSerialize(): iterable
    {
        yield [
            '1',
            '2',
            ['k' => 'v'],
            '{"phpVersion":"1","packageVersion":"2","config":{"k":"v"}}',
        ];

        yield [
            '80300',
            '2.1.37',
            ['key1' => 'value1', 'key2' => 'value2'],
            '{"phpVersion":"80300","packageVersion":"2.1.37","config":{"key1":"value1","key2":"value2"}}',
        ];
    }

    #[DataProvider('provideEquals')]
    public function testEquals(bool $expectedIsEquals, Signature $signature1, Signature $signature2): void
    {
        $isEquals = $signature1->equals($signature2);

        self::assertSame($expectedIsEquals, $isEquals);
    }

    /**
     * @return iterable<array{0: bool, 1: Signature, 2: Signature}>
     */
    public static function provideEquals(): iterable
    {
        yield [
            false,
            new Signature('1', '1', ['key' => 'value']),
            new Signature('2', '1', ['key' => 'value']),
        ];

        yield [
            false,
            new Signature('1.2.3', '1', ['key' => 'value']),
            new Signature('1.2.30', '1', ['key' => 'value']),
        ];

        yield [
            false,
            new Signature('1', '1', ['key' => 'value']),
            new Signature('1', '2', ['key' => 'value']),
        ];

        yield [
            false,
            new Signature('1', '1.2.3', ['key' => 'value']),
            new Signature('1', '1.2.2', ['key' => 'value']),
        ];

        yield [
            false,
            new Signature('1', '1', ['key' => 'value']),
            new Signature('1', '1', ['key2' => 'value']),
        ];

        yield [
            false,
            new Signature('1', '1', ['key' => 'value']),
            new Signature('1', '1', ['key' => 'value2']),
        ];

        yield [
            false,
            new Signature('1', '1', ['key' => 'value', 'key2' => 123, 'key3' => ['k' => 'v']]),
            new Signature('1', '1', ['key' => 'value', 'key2' => 123, 'key3' => ['k' => 'v2']]),
        ];

        yield [
            true,
            new Signature('1', '1', ['key' => 'value', 'key2' => 123, 'key3' => ['k' => 'v']]),
            new Signature('1', '1', ['key' => 'value', 'key2' => 123, 'key3' => ['k' => 'v']]),
        ];
    }
}
