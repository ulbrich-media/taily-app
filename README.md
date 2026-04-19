# taily-app

Installation scaffold for [Taily](https://github.com/ulbrich-media/taily) — an animal welfare organization management tool. Self-hosted, runs on shared hosting, installs via Composer.

This repository is not the application itself. It is the minimal project skeleton operators use to set up a Taily instance. The application core is pulled in as a Composer package from `ulbrich-media/taily`.

## Requirements

- PHP 8.2+
- A web server with `mod_rewrite` enabled (Apache) or equivalent
- A MySQL/MariaDB database

## Installation

```bash
composer create-project ulbrich-media/taily-app my-taily
cd my-taily
```

Composer will automatically generate an app key during installation. Before starting, configure your environment:

```bash
cp .env.example .env  # if not already created
```

Edit `.env` and set at minimum:

```
APP_URL=https://your-domain.example
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Then run the initial migration:

```bash
php artisan migrate
```

Point your web server's document root to the `public/` directory.

## Updating

```bash
composer update ulbrich-media/taily
```

This automatically publishes updated frontend assets and runs any new database migrations. Your `.env`, `storage/`, and published config overrides are never touched.

## Configuration

A `config/taily.php` file is published on first install. The available options are documented inline in that file.

## More Information

See the [Taily repository](https://github.com/ulbrich-media/taily) for the full project documentation, changelog, and issue tracker.