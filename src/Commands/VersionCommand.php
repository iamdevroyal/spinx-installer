<?php

declare(strict_types=1);

namespace Spinx\Installer\Commands;

use Spinx\Installer\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * `spinx version` — display installer version.
 *
 * Also exposed via the standard `--version` / `-V` global flag that
 * Symfony Console registers automatically on every application.
 */
final class VersionCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('version')
            ->setDescription('Display the Spinx Installer version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf(
            'Spinx Installer <info>%s</info>',
            Application::VERSION,
        ));

        return Command::SUCCESS;
    }
}
