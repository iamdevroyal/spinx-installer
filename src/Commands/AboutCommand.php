<?php

declare(strict_types=1);

namespace Spinx\Installer\Commands;

use Spinx\Installer\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * `spinx about` — display framework information.
 */
final class AboutCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('about')
            ->setDescription('Display information about Spinx and this installer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('');
        $output->writeln('<fg=magenta;options=bold>' . Application::ASCII_LOGO . '</>');
        $output->writeln('');
        $output->writeln('  <fg=white;options=bold>Spinx PHP Framework</> <fg=gray>— v' . Application::VERSION . '</>');
        $output->writeln('  <fg=gray>Created by</> <fg=magenta;options=bold>' . Application::AUTHOR . '</>');
        $output->writeln('  <fg=gray>' . Application::TAGLINE . '</>');
        $output->writeln('');
        $output->writeln(sprintf('  <fg=gray>Installer Version  :</> <options=bold>%s</>', Application::VERSION));
        $output->writeln(sprintf('  <fg=gray>Framework Package  :</> <options=bold>%s</>', Application::FRAMEWORK_PACKAGE));
        $output->writeln(sprintf('  <fg=gray>PHP Requirement    :</> <options=bold>%s</>', Application::FRAMEWORK_PHP_REQUIREMENT));
        $output->writeln(sprintf('  <fg=gray>Your PHP Version   :</> <options=bold>%s</>', PHP_VERSION));
        $output->writeln('');
        $output->writeln(sprintf('  <fg=gray>Docs   :</> %s', Application::DOCS_URL));
        $output->writeln(sprintf('  <fg=gray>GitHub :</> %s', Application::GITHUB_URL));
        $output->writeln('');
        $output->writeln('  <fg=gray>Create a new application:</>');
        $output->writeln('');
        $output->writeln('    <info>spinx new my-app</info>');
        $output->writeln('    <info>spinx new my-app --frontend=vue</info>');
        $output->writeln('    <info>spinx new my-app --frontend=react</info>');
        $output->writeln('    <info>spinx new my-app --no-interaction</info>');
        $output->writeln('');

        return Command::SUCCESS;
    }
}
