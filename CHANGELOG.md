# Changelog

All notable changes to `spinxphp/installer` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2026-08-27

### Added
- Initial release of the official Spinx global installer
- `spinx new <name>` — create a new Spinx application via `composer create-project`
- `spinx version` — display installer version
- `spinx about` — display framework and installer information
- `--frontend=vue|react|none` option for `spinx new`
- `--version=x.y.z` option for `spinx new`
- `--no-interaction` / `-n` support for CI/CD environments
- Cross-platform Composer executable detection (Windows .bat, Unix PATH, COMPOSER env, composer.phar)
- Project name validation (path traversal, OS-reserved characters, empty names)
- Existing directory protection (rejects non-empty target directories)
- Environment variable injection (`SPINX_NO_INTERACTION`, `SPINX_FRONTEND`) for headless framework wizard operation
- PHPUnit 11 test suite
- GitHub Actions CI (PHP 8.2, 8.3, 8.4 on Linux and Windows)
