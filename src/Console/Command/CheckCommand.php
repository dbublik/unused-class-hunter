<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Console\Command;

use DBublik\UnusedClass\Config;
use DBublik\UnusedClass\Console\Reporter\ReportFactory;
use DBublik\UnusedClass\UnusedClassFinder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'check',
    description: 'Check classes that are not used in any config and in the code.',
)]
final class CheckCommand extends Command
{
    public function __construct(
        private readonly ReportFactory $reportFactory = new ReportFactory(),
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('config', null, InputOption::VALUE_REQUIRED, 'The path to a config file.');
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, 'To output results in other formats.', 'txt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(
            $input,
            $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output
        );

        $config = new Config();

        if (null !== $configPath = $input->getOption('config')) {
            if (!file_exists((string) $configPath)) {
                $io->error('The config file is not exists');

                return Command::FAILURE;
            }

            $config = include $configPath;

            if (!$config instanceof Config) {
                $io->error(
                    \sprintf('The config file: "%s" does not return a "%s" instance.', $configPath, Config::class)
                );

                return Command::FAILURE;
            }
        }

        $unusedClasses = (new UnusedClassFinder($config))->findClasses($io);

        $reporter = $this->reportFactory->getReporter($input->getOption('format'));
        $report = $reporter->generate($unusedClasses);

        $output->isDecorated() ? $output->write($report) : $output->write($report, false, OutputInterface::OUTPUT_RAW);

        return Command::SUCCESS;
    }
}
