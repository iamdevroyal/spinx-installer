# Spinx Installer

The official global application installer for the [Spinx PHP Framework](https://spinxphp.pages.dev).

## Installation

Install the global installer once:

```bash
composer global require spinxphp/installer
```

Ensure your Composer global bin directory is in your `PATH`. On most systems this is:
- **Linux/macOS:** `~/.composer/vendor/bin` or `~/.config/composer/vendor/bin`
- **Windows:** `%APPDATA%\Composer\vendor\bin`

Verify the installation:

```bash
spinx --version
# Spinx Installer 1.0.0
```

---

## Creating a New Application

```bash
spinx new my-app
```

An interactive wizard guides you through:
1. Frontend adapter (Vue 3 / React 19)
2. Database driver (SQLite, MySQL, PostgreSQL)
3. Runtime driver (RoadRunner / Swoole)
4. App URL

Once complete:

```bash
cd my-app
php spinx serve
```

Visit `http://localhost:8080`.

---

## Frontend Presets

```bash
# Vue 3 + Vite (default)
spinx new my-app --frontend=vue

# React 19 + Vite
spinx new my-app --frontend=react

# No frontend (API / server-side only)
spinx new my-app --frontend=none
```

---

## Specific Framework Version

```bash
spinx new my-app --version=1.0.0
```

---

## Non-Interactive Mode (CI/CD)

```bash
spinx new my-app --frontend=vue --no-interaction
```

All prompts are skipped. Defaults used:
- DB: SQLite (zero-config)
- Runtime: RoadRunner
- URL: `http://localhost:8080`

---

## All Commands

```bash
spinx new <name>     # Create a new Spinx application
spinx version        # Display installer version
spinx about          # Display framework information
spinx --version      # Alias for version
spinx --help         # List all commands
```

---

## Alternative Installation (without global installer)

The global installer delegates to Composer internally. You can also install directly:

```bash
composer create-project spinxphp/framework my-app
```

The same interactive wizard runs either way.

---

## After Installation

The local Spinx CLI provides all development commands inside your project:

```bash
cd my-app

php spinx serve                        # Start development server
php spinx make:module Orders --all     # Scaffold DDD module
php spinx migrate                      # Run migrations
php spinx queue:work                   # Start queue worker
php spinx ai:build "Build a CMS"       # AI-powered feature generation
```

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | >= 8.2 |
| Composer | >= 2.0 |
| Extensions | mbstring, pdo, json |

---

## License

MIT — see [LICENSE](LICENSE).
