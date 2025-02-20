<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Command;

use DBublik\UnusedClassHunter\Console\ConfigurationResolver;
use DBublik\UnusedClassHunter\UnusedClassFinder;
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
    #[\Override]
    protected function configure(): void
    {
        $this->addOption('config', null, InputOption::VALUE_REQUIRED, 'The path to a config file.');
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, 'To output results in other formats.');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(
            $input,
            $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output
        );

        $resolver = new ConfigurationResolver([
            'config' => $input->getOption('config'),
            'format' => $input->getOption('format'),
        ]);

        try {
            $config = $resolver->getConfig();
            $reporter = $resolver->getReporter();
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        foreach ($config->getBootstrapFiles() as $bootstrapFile) {
            require_once $bootstrapFile;
        }

        $finder = new UnusedClassFinder($config);
        $unusedClasses = $finder->findClasses($io);

        $report = $reporter->generate($unusedClasses);

        $output->isDecorated() ? $output->write($report) : $output->write($report, false, OutputInterface::OUTPUT_RAW);

        return Command::SUCCESS;
    }
}
