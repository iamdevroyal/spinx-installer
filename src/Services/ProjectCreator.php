<?php

declare(strict_types=1);

namespace Spinx\Installer\Services;

use Spinx\Installer\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Orchestrates new Spinx project creation.
 *
 * Responsibilities:
 *  1. Run pre-flight checks (PHP, Composer, name, directory)
 *  2. Invoke `composer create-project spinxphp/framework <target>`
 *  3. Pass non-interactive configuration to the framework's own Installer
 *     via environment variables (SPINX_NO_INTERACTION, SPINX_FRONTEND, etc.)
 *  4. Report progress and errors
 *
 * The framework's post-create-project-cmd hook (Spinx\Installer\Installer)
 * handles all deep initialization: .env writing, spinx.json updates,
 * RoadRunner download, and initial migrations. We do NOT duplicate that logic.
 */
final class ProjectCreator
{
    private const FRAMEWORK_PACKAGE = Application::FRAMEWORK_PACKAGE;

    public function __construct(
        private readonly Composer $composer,
        private readonly Environment $environment,
        private readonly OutputInterface $output,
    ) {}

    /**
     * Create a new Spinx project.
     *
     * @param string      $name           Project directory name (validated)
     * @param string      $cwd            Resolved absolute working directory
     * @param string|null $version        Specific framework version, or null for latest stable
     * @param string      $frontend       'vue' | 'react' | 'none'
     * @param bool        $noInteraction  Skip all interactive prompts in the framework wizard
     *
     * @throws \RuntimeException|\InvalidArgumentException on validation or Composer failure
     */
    public function create(
        string $name,
        string $cwd,
        ?string $version,
        string $frontend,
        bool $noInteraction,
    ): void {
        // ── 1. Pre-flight validation ─────────────────────────────────────
        $this->environment->checkPhpVersion();
        $this->environment->checkExtensions();
        $this->environment->validateProjectName($name);

        $targetDir = rtrim($cwd, '/\\') . DIRECTORY_SEPARATOR . $name;
        $this->environment->validateTargetDirectory($targetDir);
        $this->environment->validateFrontend($frontend);

        // ── 2. Composer availability ─────────────────────────────────────
        $this->composer->requireAvailable();

        // ── 3. Create project ────────────────────────────────────────────
        $this->writeLine("  <fg=default>◌ Installing Spinx framework...</>");

        // Environment variables forwarded into the composer create-project
        // subprocess. The framework Installer reads these so it can skip STDIN
        // prompts in non-interactive mode while still calling the same wizard.
        $env = [
            'SPINX_FRONTEND'        => $frontend,
            'SPINX_NO_INTERACTION'  => $noInteraction ? 'true' : 'false',
        ];

        $success = $this->composer->createProject(
            package:          self::FRAMEWORK_PACKAGE,
            targetDir:        $targetDir,
            version:          $version,
            env:              $env,
            outputCallback:   $this->makeOutputCallback(),
        );

        if (!$success) {
            throw new \RuntimeException(
                "Composer failed to create the project.\n\n" .
                "This is usually caused by:\n" .
                "  • No internet connection\n" .
                "  • An invalid version constraint (\"--version={$version}\")\n" .
                "  • Packagist or GitHub being temporarily unavailable\n\n" .
                "Run the command again with -v for verbose Composer output."
            );
        }

        // ── 4. Success output ────────────────────────────────────────────
        $this->printSuccess($name, $frontend);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function printSuccess(string $name, string $frontend): void
    {
        $this->newLine();
        $this->writeLine("  <fg=green;options=bold>✔ Spinx application created successfully!</>");
        $this->newLine();
        $this->writeLine("  <fg=gray>─────────────────────────────────────</>");
        $this->writeLine("  <fg=default>  App name : </><fg=white;options=bold>{$name}</>");
        if ($frontend !== 'none') {
            $this->writeLine("  <fg=default>  Frontend : </><fg=white;options=bold>{$frontend}</>");
        }
        $this->writeLine("  <fg=gray>─────────────────────────────────────</>");
        $this->newLine();
        $this->writeLine("  <fg=default;options=bold>Next steps:</>");
        $this->newLine();
        $this->writeLine("  <fg=gray>    cd {$name}</>");
        if ($frontend !== 'none') {
            $this->writeLine("  <fg=gray>    cd frontend && npm install && cd ..</>");
        }
        $this->writeLine("  <fg=magenta;options=bold>    php spinx serve</>");
        $this->newLine();
        $this->writeLine("  <fg=gray>  Docs  : " . Application::DOCS_URL . "</>");
        $this->writeLine("  <fg=gray>  GitHub: " . Application::GITHUB_URL . "</>");
        $this->newLine();
    }

    /**
     * Build a callback that streams Composer output through Symfony Console's
     * output formatter, preserving existing ANSI codes from Composer itself.
     */
    private function makeOutputCallback(): callable
    {
        return function (string $type, string $buffer): void {
            // Pass raw buffer directly — Composer already handles its own ANSI
            if ($type === Process::ERR) {
                $this->output->write("<fg=yellow>{$buffer}</>");
            } else {
                $this->output->write($buffer);
            }
        };
    }

    private function writeLine(string $text): void
    {
        $this->output->writeln($text);
    }

    private function newLine(): void
    {
        $this->output->writeln('');
    }
}
