<?php

declare(strict_types=1);

namespace Spinx\Installer\Commands;

use Spinx\Installer\Application;
use Spinx\Installer\Services\Composer;
use Spinx\Installer\Services\Environment;
use Spinx\Installer\Services\ProjectCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * `spinx new <name>` — create a new Spinx application.
 *
 * Usage:
 *   spinx new my-app
 *   spinx new my-app --frontend=vue
 *   spinx new my-app --frontend=react
 *   spinx new my-app --frontend=none
 *   spinx new my-app --version=1.0.0
 *   spinx new my-app --no-interaction
 *   spinx new my-app -n
 */
final class NewCommand extends Command
{
    protected static string $defaultName = 'new';

    protected function configure(): void
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Spinx application')
            ->addArgument(
                name:        'name',
                mode:        InputArgument::OPTIONAL,
                description: 'The name of the new application directory',
            )
            ->addOption(
                name:        'frontend',
                shortcut:    'f',
                mode:        InputOption::VALUE_REQUIRED,
                description: 'Frontend preset: vue (default), react, or none',
                default:     null,
            )
            ->addOption(
                name:        'version',
                shortcut:    null,
                mode:        InputOption::VALUE_REQUIRED,
                description: 'Specific Spinx framework version to install (e.g. 1.0.0)',
                default:     null,
            )
            ->setHelp(<<<'HELP'
The <info>new</info> command creates a new Spinx application using Composer.

  <info>spinx new my-app</info>
  <info>spinx new my-app --frontend=vue</info>
  <info>spinx new my-app --frontend=react</info>
  <info>spinx new my-app --frontend=none</info>
  <info>spinx new my-app --version=1.0.0</info>
  <info>spinx new my-app --no-interaction</info>

After creation, start your application:

  <info>cd my-app</info>
  <info>php spinx serve</info>
HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $env = new Environment();

        // ── Banner ────────────────────────────────────────────────────────
        $this->printBanner($output);

        // ── Resolve project name ──────────────────────────────────────────
        $name = $input->getArgument('name');

        if ($name === null || $name === '') {
            if ($input->isInteractive()) {
                $q = new Question("  <options=bold>What is your application name?</> › ");
                $q->setValidator(function (?string $v) use ($env): string {
                    $env->validateProjectName((string) $v);
                    return (string) $v;
                });
                $name = $io->askQuestion($q);
            } else {
                $io->error('Application name is required. Usage: spinx new <name>');
                return Command::FAILURE;
            }
        }

        // Validate name (even when provided as argument)
        try {
            $env->validateProjectName((string) $name);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        // ── Resolve frontend ──────────────────────────────────────────────
        $frontend = $input->getOption('frontend');

        if ($frontend === null) {
            if ($input->isInteractive()) {
                $q = new ChoiceQuestion(
                    "  <options=bold>Which frontend adapter would you like?</>",
                    ['vue' => 'Vue 3 (default)', 'react' => 'React 19', 'none' => 'None (API only)'],
                    'vue',
                );
                $frontend = $io->askQuestion($q);
            } else {
                $frontend = 'vue'; // Default for non-interactive mode
            }
        }

        try {
            $env->validateFrontend((string) $frontend);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        // ── Run creation ──────────────────────────────────────────────────
        $cwd     = (string) getcwd();
        $version = $input->getOption('version');
        $noInteraction = !$input->isInteractive();

        $io->writeln(sprintf(
            "  Creating application <options=bold>%s</>...",
            $name,
        ));
        $io->newLine();

        try {
            $creator = new ProjectCreator(
                composer:    new Composer(),
                environment: $env,
                output:      $output,
            );

            $creator->create(
                name:          (string) $name,
                cwd:           $cwd,
                version:       $version !== null ? (string) $version : null,
                frontend:      (string) $frontend,
                noInteraction: $noInteraction,
            );
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function printBanner(OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<fg=magenta;options=bold>' . Application::ASCII_LOGO . '</>');
        $output->writeln('');
        $output->writeln('  <fg=white;options=bold>Spinx PHP Framework</> <fg=gray>— v' . Application::VERSION . '</>');
        $output->writeln('  <fg=gray>Created by</> <fg=magenta;options=bold>' . Application::AUTHOR . '</>');
        $output->writeln('  <fg=gray>' . Application::TAGLINE . '</>');
        $output->writeln('');
    }
}
