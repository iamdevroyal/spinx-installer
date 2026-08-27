<?php

declare(strict_types=1);

namespace Spinx\Installer\Services;

use Spinx\Installer\Application;

/**
 * Pre-flight environment checks.
 *
 * Verifies that the host machine meets the minimum requirements to both
 * run the installer and bootstrap the newly created Spinx project.
 */
final class Environment
{
    /**
     * Check PHP version.
     *
     * @throws \RuntimeException if PHP version is below the framework's minimum
     */
    public function checkPhpVersion(): void
    {
        // Framework requires >= 8.2
        if (PHP_VERSION_ID < 80200) {
            throw new \RuntimeException(sprintf(
                "Spinx requires PHP %s or higher. You are running PHP %s.\n" .
                "Upgrade your PHP installation and try again.",
                Application::FRAMEWORK_PHP_REQUIREMENT,
                PHP_VERSION,
            ));
        }
    }

    /**
     * Check that required PHP extensions are loaded.
     *
     * Only the extensions that the Spinx framework itself declares as hard
     * requirements in its composer.json are checked here. Do not add speculative
     * extension checks that the framework does not actually enforce.
     *
     * @throws \RuntimeException listing all missing extensions
     */
    public function checkExtensions(): void
    {
        $required = ['mbstring', 'pdo', 'json'];
        $missing  = array_filter($required, static fn (string $ext) => !extension_loaded($ext));

        if ($missing !== []) {
            throw new \RuntimeException(
                "The following PHP extensions are required by Spinx but are not loaded:\n\n" .
                implode("\n", array_map(static fn (string $e) => "  • ext-{$e}", $missing)) . "\n\n" .
                "Enable them in your php.ini and try again."
            );
        }
    }

    /**
     * Validate that the chosen project name is safe.
     *
     * Rejects:
     *  - empty strings
     *  - path separators (/ \)
     *  - path traversal (../)
     *  - OS-reserved characters on Windows (< > : " | ? *)
     *  - names that start with a dot or hyphen
     *  - whitespace
     *
     * Accepts: letters, digits, hyphens, underscores (e.g. blog, my-app, shop_api)
     *
     * @throws \InvalidArgumentException with a user-friendly explanation
     */
    public function validateProjectName(string $name): void
    {
        if ($name === '') {
            throw new \InvalidArgumentException('Project name must not be empty.');
        }

        if (preg_match('/[\/\\\]/', $name)) {
            throw new \InvalidArgumentException(
                "Invalid project name \"{$name}\".\n" .
                "Project names must not contain path separators (/ or \\).\n" .
                "Did you mean to use `spinx new " . basename($name) . "`?"
            );
        }

        if (str_contains($name, '..')) {
            throw new \InvalidArgumentException(
                "Invalid project name \"{$name}\".\n" .
                "Project names must not contain path traversal sequences (..)."
            );
        }

        // Reject OS-reserved / shell-special characters
        if (preg_match('/[<>:"|?*\s]/', $name)) {
            throw new \InvalidArgumentException(
                "Invalid project name \"{$name}\".\n" .
                "Project names must only contain letters, digits, hyphens, and underscores."
            );
        }

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_\-]*$/', $name)) {
            throw new \InvalidArgumentException(
                "Invalid project name \"{$name}\".\n" .
                "Project names must start with a letter and contain only letters, digits, hyphens, and underscores.\n" .
                "Examples: blog, my-app, shop_api"
            );
        }
    }

    /**
     * Validate that the target directory can be safely created.
     *
     * @throws \RuntimeException if the directory already exists and is not empty
     */
    public function validateTargetDirectory(string $absolutePath): void
    {
        if (!is_dir($absolutePath)) {
            return; // Directory doesn't exist — safe to create
        }

        // Directory exists — allow only if it's empty (e.g. pre-created by CI)
        $entries = array_diff((array) scandir($absolutePath), ['.', '..']);

        if ($entries !== []) {
            throw new \RuntimeException(
                "The directory \"{$absolutePath}\" already exists and is not empty.\n" .
                "Choose a different name or remove the existing directory first."
            );
        }
    }

    /**
     * Validate a frontend preset value.
     *
     * @throws \InvalidArgumentException if the value is not in the supported list
     */
    public function validateFrontend(string $frontend): void
    {
        $supported = ['vue', 'react', 'none'];

        if (!in_array($frontend, $supported, true)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid frontend \"%s\". Supported values: %s.",
                $frontend,
                implode(', ', $supported),
            ));
        }
    }
}
