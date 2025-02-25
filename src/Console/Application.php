<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console;

use DBublik\UnusedClassHunter\Console\Command\HuntCommand;
use DBublik\UnusedClassHunter\Package;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;

final class Application extends BaseApplication
{
    public const NAME = 'Unused class hunter';

    public function __construct()
    {
        parent::__construct(self::NAME, Package::VERSION);

        $this->add(new HuntCommand());
    }

    #[\Override]
    protected function getDefaultCommands(): array
    {
        return [new HelpCommand(), new ListCommand()];
    }
}
