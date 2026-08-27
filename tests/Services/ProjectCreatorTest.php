<?php

declare(strict_types=1);

namespace Spinx\Installer\Tests\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spinx\Installer\Services\Environment;

final class ProjectCreatorTest extends TestCase
{
    private Environment $env;

    protected function setUp(): void
    {
        $this->env = new Environment();
    }

    // ── Project name validation ───────────────────────────────────────────

    #[Test]
    #[DataProvider('validNames')]
    public function valid_project_names_do_not_throw(string $name): void
    {
        $this->expectNotToPerformAssertions();
        $this->env->validateProjectName($name);
    }

    /** @return array<string, array{string}> */
    public static function validNames(): array
    {
        return [
            'simple'          => ['blog'],
            'hyphenated'      => ['my-app'],
            'underscored'     => ['shop_api'],
            'alphanumeric'    => ['app2024'],
            'mixed'           => ['My-App2'],
        ];
    }

    #[Test]
    #[DataProvider('invalidNames')]
    public function invalid_project_names_throw(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->env->validateProjectName($name);
    }

    /** @return array<string, array{string}> */
    public static function invalidNames(): array
    {
        return [
            'empty'               => [''],
            'path_traversal_unix' => ['../app'],
            'deep_traversal'      => ['../../app'],
            'slash'               => ['my/app'],
            'backslash'           => ['my\\app'],
            'space'               => ['my app'],
            'question_mark'       => ['my?app'],
            'starts_with_digit'   => ['1app'],
            'starts_with_hyphen'  => ['-app'],
            'dot_dot'             => ['..'],
            'os_reserved_colon'   => ['my:app'],
        ];
    }

    // ── Existing directory protection ─────────────────────────────────────

    #[Test]
    public function non_existent_directory_passes_validation(): void
    {
        $this->expectNotToPerformAssertions();
        $this->env->validateTargetDirectory(sys_get_temp_dir() . '/spinx-test-nonexistent-' . uniqid());
    }

    #[Test]
    public function empty_existing_directory_passes_validation(): void
    {
        $dir = sys_get_temp_dir() . '/spinx-test-empty-' . uniqid();
        mkdir($dir, 0755, true);

        try {
            $this->expectNotToPerformAssertions();
            $this->env->validateTargetDirectory($dir);
        } finally {
            rmdir($dir);
        }
    }

    #[Test]
    public function non_empty_existing_directory_throws(): void
    {
        $dir = sys_get_temp_dir() . '/spinx-test-nonempty-' . uniqid();
        mkdir($dir, 0755, true);
        file_put_contents($dir . '/existing-file.txt', 'already here');

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessageMatches('/already exists and is not empty/');
            $this->env->validateTargetDirectory($dir);
        } finally {
            unlink($dir . '/existing-file.txt');
            rmdir($dir);
        }
    }

    // ── Frontend validation ───────────────────────────────────────────────

    #[Test]
    #[DataProvider('validFrontends')]
    public function valid_frontend_values_do_not_throw(string $frontend): void
    {
        $this->expectNotToPerformAssertions();
        $this->env->validateFrontend($frontend);
    }

    /** @return array<string, array{string}> */
    public static function validFrontends(): array
    {
        return [
            'vue'   => ['vue'],
            'react' => ['react'],
            'none'  => ['none'],
        ];
    }

    #[Test]
    #[DataProvider('invalidFrontends')]
    public function invalid_frontend_values_throw(string $frontend): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->env->validateFrontend($frontend);
    }

    /** @return array<string, array{string}> */
    public static function invalidFrontends(): array
    {
        return [
            'angular' => ['angular'],
            'svelte'  => ['svelte'],
            'empty'   => [''],
            'capital' => ['Vue'],
        ];
    }

    // ── PHP version check ─────────────────────────────────────────────────

    #[Test]
    public function php_version_check_passes_on_current_php(): void
    {
        // We are running on PHP >= 8.2 (installer requirement), so this must pass
        $this->expectNotToPerformAssertions();
        $this->env->checkPhpVersion();
    }

    // ── Extension checks ──────────────────────────────────────────────────

    #[Test]
    public function extension_check_passes_when_all_loaded(): void
    {
        // mbstring, pdo, json are all expected to be present in the test env
        $this->expectNotToPerformAssertions();
        $this->env->checkExtensions();
    }
}
