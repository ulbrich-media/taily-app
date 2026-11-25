# taily-app

Installation scaffold for [Taily](https://github.com/ulbrich-media/taily) — an animal welfare organization management tool. Self-hosted, runs on shared hosting, installs via Composer.

This repository is not the application itself. It is the minimal project skeleton operators use to set up a Taily instance. The application core is pulled in as a Composer package from `ulbrich-media/taily`.

## Requirements

- PHP 8.3+
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

## Scheduled Tasks

Taily runs time-based jobs (contract-signing reminders and expiry, the Tiervermittlung.de export) via Laravel's scheduler. This does not require a persistent background worker — add a single cron entry that invokes the scheduler once a minute, and it exits immediately whenever nothing is due:

```bash
* * * * * php /path-to-your-install/artisan schedule:run >> /dev/null 2>&1
```

This is required for every installation. Without it, reminder emails, signing-link expiry and the Tiervermittlung.de export will never run.

To check it: `php artisan schedule:list` shows the registered jobs and when each is next due. `schedule:run` only starts jobs due in the current minute, so "No scheduled commands are ready to run" is the normal output most of the time; `php artisan schedule:test --name=tiervermittlung:sync` runs a single job right away.

## File Uploads

The largest file Taily accepts is 100 MB. PHP, the web server and any proxy in front of Taily have their own limits, which apply **before** Taily sees a file. Make sure your hosting allows at least:

| Requirement | Minimum |
|---|---|
| PHP `upload_max_filesize` | `100M` |
| PHP `post_max_size` (must be larger than `upload_max_filesize`) | `110M` |
| PHP `max_input_time` and `max_execution_time` | `300` seconds |
| Request body size accepted by the web server and any proxy/CDN | 110 MB |

If any of these is lower, large uploads fail with an error. How to change them depends on your hosting.

## Updating

```bash
composer update ulbrich-media/taily -W
```

`-W` (`--with-all-dependencies`) lets Composer also update the packages Taily depends on, e.g. Laravel itself when Taily moves to a new major version. Without it, such an update fails with "fixed to … by a partial update".

This automatically publishes updated frontend assets and runs any new database migrations. Your `.env`, `storage/`, and published config overrides are never touched.

`bootstrap/app.php` is not updated either. Installs set up with Taily v0.14.1 or earlier configured scheduled tasks and middleware there; once Taily registers them itself, replace that file with the current one from this repository. Until then the old file keeps working, but its looser CSRF exception list stays in effect.

### Installs set up with taily-app v0.1.0

The first scaffold release did not refresh Laravel's package discovery after an update. Packages Taily added later (PDF generation, Sentry) are then installed but never loaded, which shows up as `Target class [dompdf.wrapper] does not exist` or a missing "Sentry" section in `php artisan about`. Check that your `composer.json` contains this script, and add it if not:

```json
"scripts": {
    "post-autoload-dump": [
        "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
        "@php artisan package:discover --ansi"
    ],
    ...
}
```

Then run `php artisan package:discover` once and `php artisan optimize:clear`.

## Configuration

A `config/taily.php` file is published on first install. The available options are documented inline in that file.

## More Information

See the [Taily repository](https://github.com/ulbrich-media/taily) for the full project documentation, changelog, and issue tracker.