<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console;

use DBublik\UnusedClassHunter\Console\Application;
use DBublik\UnusedClassHunter\Console\Command\HuntCommand;
use DBublik\UnusedClassHunter\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;

/**
 * @internal
 */
#[CoversClass(Application::class)]
final class ApplicationTest extends TestCase
{
    public function testConstructor(): void
    {
        $application = new Application();

        self::assertSame('Unused class hunter', $application->getName());
        self::assertSame(Package::VERSION, $application->getVersion());

        /** @var array<string, Command> $commands */
        $commands = (new \ReflectionProperty(SymfonyApplication::class, 'commands'))->getValue($application);
        self::assertSame(
            [
                HelpCommand::class,
                ListCommand::class,
                HuntCommand::class,
            ],
            array_values(
                array_map(
                    static fn (Command $command): string => $command::class,
                    $commands,
                )
            )
        );
    }
}
