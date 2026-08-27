<?php

declare(strict_types=1);

namespace Spinx\Installer\Tests\Commands;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spinx\Installer\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class NewCommandTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
    }

    // ── Missing name in non-interactive mode ──────────────────────────────

    #[Test]
    public function new_command_fails_without_name_in_non_interactive_mode(): void
    {
        $command = $this->app->find('new');
        $tester  = new CommandTester($command);

        $tester->execute(
            input:   [],
            options: ['interactive' => false],
        );

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('name is required', strtolower($tester->getDisplay()));
    }

    // ── Invalid name from argument ────────────────────────────────────────

    #[Test]
    public function new_command_fails_with_path_traversal_name(): void
    {
        $command = $this->app->find('new');
        $tester  = new CommandTester($command);

        $tester->execute(
            input:   ['name' => '../evil'],
            options: ['interactive' => false],
        );

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('path separator', strtolower($tester->getDisplay()));
    }

    #[Test]
    public function new_command_fails_with_name_containing_spaces(): void
    {
        $command = $this->app->find('new');
        $tester  = new CommandTester($command);

        $tester->execute(
            input:   ['name' => 'my app'],
            options: ['interactive' => false],
        );

        $this->assertSame(1, $tester->getStatusCode());
    }

    // ── Invalid frontend option ───────────────────────────────────────────

    #[Test]
    public function new_command_fails_with_invalid_frontend(): void
    {
        $command = $this->app->find('new');
        $tester  = new CommandTester($command);

        $tester->execute(
            input:   ['name' => 'valid-name', '--frontend' => 'angular'],
            options: ['interactive' => false],
        );

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('invalid frontend', strtolower($tester->getDisplay()));
    }

    // ── Version command ───────────────────────────────────────────────────

    #[Test]
    public function version_command_outputs_installer_version(): void
    {
        $command = $this->app->find('version');
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString(Application::VERSION, $tester->getDisplay());
        $this->assertStringContainsStringIgnoringCase('spinx installer', $tester->getDisplay());
    }

    // ── About command ─────────────────────────────────────────────────────

    #[Test]
    public function about_command_outputs_framework_info(): void
    {
        $command = $this->app->find('about');
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());

        $display = $tester->getDisplay();
        $this->assertStringContainsString(Application::VERSION, $display);
        $this->assertStringContainsString(Application::FRAMEWORK_PACKAGE, $display);
        $this->assertStringContainsString('spinx new', $display);
    }
}
