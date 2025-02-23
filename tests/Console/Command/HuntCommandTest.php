<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Command;

use DBublik\UnusedClassHunter\Console\Command\HuntCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(HuntCommand::class)]
final class HuntCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new HuntCommand();

        self::assertSame('hunt', $command->getName());
        self::assertSame(
            'Find and delete classes that are not used in any config and in the code.',
            $command->getDescription()
        );
        self::assertSame(
            [
                [
                    'name' => 'config',
                    'shortcut' => null,
                    'mode' => InputOption::VALUE_REQUIRED,
                    'description' => 'The path to a config file.',
                    'default' => null,
                ],
                [
                    'name' => 'format',
                    'shortcut' => null,
                    'mode' => InputOption::VALUE_REQUIRED,
                    'description' => 'To output results in other formats.',
                    'default' => null,
                ],
                [
                    'name' => 'delete',
                    'shortcut' => null,
                    'mode' => InputOption::VALUE_NEGATABLE,
                    'description' => 'Delete all classes that are not used.',
                    'default' => null,
                ],
            ],
            array_map(
                static fn (InputOption $option): array => [
                    'name' => $option->getName(),
                    'shortcut' => $option->getShortcut(),
                    'mode' => (new \ReflectionProperty(InputOption::class, 'mode'))->getValue($option),
                    'description' => $option->getDescription(),
                    'default' => $option->getDefault(),
                ],
                array_values($command->getDefinition()->getOptions()),
            )
        );
    }

    public function testExecuteInvalidConfigPath(): void
    {
        $command = new HuntCommand();
        $commandTester = new CommandTester($command);
        $configFile = __DIR__ . '/../../Fixtures/Not/Found';

        $exitCode = $commandTester->execute(
            ['--config' => $configFile],
            ['capture_stderr_separately' => true]
        );

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('[ERROR] The config file does not exist', $commandTester->getErrorOutput());
    }

    public function testExecuteInvalidFormat(): void
    {
        $command = new HuntCommand();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(
            ['--format' => 'invalid'],
            ['capture_stderr_separately' => true]
        );

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('[ERROR] Unsupported format', $commandTester->getErrorOutput());
    }

    public function testExecute(): void
    {
        $command = new HuntCommand();
        $commandTester = new CommandTester($command);
        $configFile = __DIR__ . '/../../Fixtures/Console/Command/HuntCommand/config-empty-directory.php';

        $exitCode = $commandTester->execute(['--config' => $configFile]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString(
            "\nSuccess! The hunt is over — no unused classes found.",
            $commandTester->getDisplay()
        );
        self::assertMatchesRegularExpression(
            '/Duration \d{1,2}\.\d{3} seconds, \d{1,3}\.\d{2} MB memory used/',
            $commandTester->getDisplay()
        );
        self::assertSame(['bootstrap-file-was-included'], $_SERVER['argv'] ?? []);
    }

    public function testExecuteDecorated(): void
    {
        $command = new HuntCommand();
        $commandTester = new CommandTester($command);
        $configFile = __DIR__ . '/../../Fixtures/Console/Command/HuntCommand/config-empty-directory.php';

        $exitCode = $commandTester->execute(['--config' => $configFile], ['decorated' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString(
            '[32mSuccess! The hunt is over — no unused classes found.',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteWithDeletion(): void
    {
        $command = new HuntCommand();
        $commandTester = new CommandTester($command);
        $configFile = __DIR__ . '/../../Fixtures/Console/Command/HuntCommand/config-empty-directory.php';
        $file = __DIR__ . '/../../Fixtures/Console/Command/HuntCommand/empty-directory/'
            . uniqid('unused-class-hunter-test_', true) . '.php';
        copy(__FILE__, $file);

        try {
            $exitCode = $commandTester->execute(['--config' => $configFile, '--delete' => true]);
            self::assertSame(Command::SUCCESS, $exitCode);
            self::assertStringContainsString(
                'The hunt is over! 1 unused classes deleted.',
                $commandTester->getDisplay()
            );
            self::assertFileDoesNotExist($file);
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
