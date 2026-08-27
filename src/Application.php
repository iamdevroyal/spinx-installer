<?php

declare(strict_types=1);

namespace Spinx\Installer;

use Spinx\Installer\Commands\AboutCommand;
use Spinx\Installer\Commands\NewCommand;
use Spinx\Installer\Commands\VersionCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * The Spinx Installer Symfony Console application.
 *
 * Registered commands:
 *   - new       — scaffold a new Spinx project
 *   - version   — display installer version
 *   - about     — display framework information
 */
final class Application extends BaseApplication
{
    public const VERSION = '1.0.0';

    public const FRAMEWORK_PACKAGE = 'spinxphp/spinx';

    public const FRAMEWORK_PHP_REQUIREMENT = '>=8.2';

    public const DOCS_URL = 'https://spinxphp.pages.dev/docs';

    public const GITHUB_URL = 'https://github.com/iamdevroyal/spinxphp';

    public const AUTHOR = 'Njoku Royal Nnaemeka (@iamdevroyal)';

    public const TAGLINE = 'High-Performance Persistent Workers • Enforced DDD • Reactive Islands';

    public const ASCII_LOGO = <<<'LOGO'
  ____        _           
 / ___| _ __ (_)_ __ __  __
 \___ \| '_ \| | '_ \\ \/ /
  ___) | |_) | | | | |>  < 
 |____/| .__/|_|_| |_/_/\_\
       |_|
LOGO;

    public function __construct()
    {
        parent::__construct('Spinx Installer', self::VERSION);

        $this->addCommands([
            new NewCommand(),
            new VersionCommand(),
            new AboutCommand(),
        ]);

        $this->setDefaultCommand('list');
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display help for the given command'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--ansi', '', InputOption::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', null),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ]);
    }
}
