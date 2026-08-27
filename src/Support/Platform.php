<?php

declare(strict_types=1);

namespace Spinx\Installer\Support;

/**
 * Platform-specific detection utilities.
 *
 * Handles cross-platform concerns such as Composer executable resolution
 * and OS detection — ensuring the installer works correctly on Windows,
 * Linux, and macOS without shell-specific assumptions.
 */
final class Platform
{
    public static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    /**
     * Resolve the Composer executable path.
     *
     * Search order:
     *  1. COMPOSER environment variable (user-set override)
     *  2. composer.bat / composer.cmd (Windows PATH)
     *  3. composer (Unix PATH)
     *  4. composer.phar in CWD or common locations
     *
     * @throws \RuntimeException if Composer cannot be found
     */
    public static function composerExecutable(): string
    {
        // 1. Explicit override
        $envComposer = getenv('COMPOSER');
        if ($envComposer !== false && $envComposer !== '' && self::executableExists($envComposer)) {
            return $envComposer;
        }

        // 2. Windows .bat / .cmd shims
        if (self::isWindows()) {
            foreach (['composer.bat', 'composer.cmd', 'composer'] as $candidate) {
                if (self::inPath($candidate)) {
                    return $candidate;
                }
            }
        }

        // 3. Unix `composer` in PATH
        if (self::inPath('composer')) {
            return 'composer';
        }

        // 4. composer.phar fallbacks
        $pharLocations = [
            getcwd() . '/composer.phar',
            $_SERVER['HOME'] ?? '',
        ];
        foreach ($pharLocations as $dir) {
            $phar = rtrim((string) $dir, '/\\') . '/composer.phar';
            if (is_file($phar)) {
                return PHP_BINARY . ' ' . $phar;
            }
        }

        throw new \RuntimeException(
            "Composer was not found. Install it from https://getcomposer.org and ensure it is in your PATH."
        );
    }

    /**
     * Check whether an executable name is reachable via PATH.
     */
    public static function inPath(string $executable): bool
    {
        if (self::isWindows()) {
            $result = shell_exec('where ' . escapeshellarg($executable) . ' 2>nul');
        } else {
            $result = shell_exec('which ' . escapeshellarg($executable) . ' 2>/dev/null');
        }

        return $result !== null && trim((string) $result) !== '';
    }

    /**
     * Check whether a full path to an executable is valid and executable.
     */
    public static function executableExists(string $path): bool
    {
        return is_file($path) && is_executable($path);
    }

    /**
     * Return the PHP binary used to run the current process.
     * Symfony\Component\Process handles this natively but we expose it
     * here for callers that need it separately.
     */
    public static function phpBinary(): string
    {
        return PHP_BINARY;
    }
}
