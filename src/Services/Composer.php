<?php

declare(strict_types=1);

namespace Spinx\Installer\Services;

use Spinx\Installer\Application;
use Spinx\Installer\Support\Platform;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Wraps Composer interactions.
 *
 * All Composer invocations use symfony/process with argument arrays — no
 * raw shell command strings are ever constructed from user input, preventing
 * shell injection attacks even if a malicious project name is provided.
 */
final class Composer
{
    private readonly string $executable;

    public function __construct()
    {
        $this->executable = $this->resolveExecutable();
    }

    /**
     * Run `composer create-project <package> <directory> [version]`.
     *
     * @param string        $package    Composer package name (e.g. spinxphp/framework)
     * @param string        $targetDir  Absolute path to the new project directory
     * @param string|null   $version    Specific version constraint, or null for latest stable
     * @param array<string,string> $env Additional environment variables to pass to the subprocess
     * @param callable|null $outputCallback Receives ($type, $buffer) for streaming output
     */
    public function createProject(
        string $package,
        string $targetDir,
        ?string $version = null,
        array $env = [],
        ?callable $outputCallback = null,
    ): bool {
        $packageSpec = $version !== null ? "{$package}:{$version}" : $package;

        // Use argument arrays — Symfony Process handles escaping correctly
        // across Windows and Unix. Never concatenate user input into a shell string.
        $cmd = [
            $this->executable,
            'create-project',
            $packageSpec,
            $targetDir,
            '--no-interaction',
            '--ansi',
        ];

        $process = new Process(
            command: $cmd,
            cwd: dirname($targetDir),
            env: array_merge($_SERVER, $_ENV, $env),
            timeout: 600, // 10 min max for slow connections
        );

        $process->run($outputCallback ?? static function (string $type, string $buffer): void {
            // Default: stream directly to STDOUT/STDERR
            if ($type === Process::ERR) {
                fwrite(STDERR, $buffer);
            } else {
                fwrite(STDOUT, $buffer);
            }
        });

        return $process->isSuccessful();
    }

    /**
     * Retrieve the resolved Composer version string.
     */
    public function version(): ?string
    {
        $process = new Process([$this->executable, '--version', '--no-ansi']);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        // e.g. "Composer version 2.7.6 2024-05-04 ..."
        if (preg_match('/Composer version (\S+)/', $process->getOutput(), $m)) {
            return $m[1];
        }

        return trim($process->getOutput()) ?: null;
    }

    /**
     * Check that Composer is available; throw a descriptive exception if not.
     *
     * @throws \RuntimeException
     */
    public function requireAvailable(): void
    {
        $version = $this->version();

        if ($version === null) {
            throw new \RuntimeException(
                "Composer is not responding correctly.\n" .
                "Install Composer from https://getcomposer.org and ensure it is in your PATH."
            );
        }
    }

    // ── private ─────────────────────────────────────────────────────────

    private function resolveExecutable(): string
    {
        // symfony/process provides ExecutableFinder as a clean cross-platform
        // alternative, but we still fall back to our own Platform helper so
        // that composer.phar-only setups and COMPOSER env overrides are covered.
        $finder = new ExecutableFinder();

        $candidates = Platform::isWindows()
            ? ['composer.bat', 'composer.cmd', 'composer']
            : ['composer'];

        foreach ($candidates as $name) {
            $found = $finder->find($name);
            if ($found !== null) {
                return $found;
            }
        }

        // Fallback to Platform helper (handles composer.phar, COMPOSER env var)
        return Platform::composerExecutable();
    }
}
