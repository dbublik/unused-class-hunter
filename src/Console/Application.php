<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console;

use DBublik\UnusedClassHunter\Console\Command\CheckCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;

final class Application extends BaseApplication
{
    public const string NAME = 'Unused class hunter';
    public const string VERSION = '1.0.0';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->add(new CheckCommand());
    }

    #[\Override]
    protected function getDefaultCommands(): array
    {
        return [new HelpCommand(), new ListCommand()];
    }
}
