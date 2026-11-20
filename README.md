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

## Scheduled Tasks

Taily runs time-based jobs (e.g. contract-signing reminders and expiry) via Laravel's scheduler. This does not require a persistent background worker — add a single cron entry that invokes the scheduler once a minute, and it exits immediately whenever nothing is due:

```bash
* * * * * php /path-to-your-install/artisan schedule:run >> /dev/null 2>&1
```

This is required for every installation. Without it, reminder emails and signing-link expiry will never run.

## File Uploads

Taily accepts these uploads:

| Upload | Maximum size |
|---|---|
| Animal and person pictures | 10 MB |
| Adoption contracts (PDF/image) | 20 MB |
| Animal videos (mp4, webm, mov) | 100 MB |

PHP and the web server have their own, usually much lower, limits that apply **before** Taily sees a file. If they are lower than the table above, large uploads fail with an error. Raise them to the following values.

### PHP

| Setting | Value | Why |
|---|---|---|
| `upload_max_filesize` | `100M` | Largest single file (a video). |
| `post_max_size` | `110M` | Whole request incl. form data, so it must be a bit larger than `upload_max_filesize`. |
| `max_input_time` | `300` | Seconds PHP waits for the upload to arrive. Slow connections need more than the usual 60. |
| `max_execution_time` | `300` | Seconds a request may run. Storing large files and creating image previews takes time. |

`memory_limit` does not need to grow: uploads are streamed to a temporary file, not loaded into memory.

Where to set these depends on your hosting:

- **Hosting control panel** (most shared hosting): look for "PHP settings" or "PHP configuration". This is usually the easiest way.
- **`.user.ini`** (PHP-FPM / CGI, common on shared hosting): create `public/.user.ini`:
  ```ini
  upload_max_filesize = 100M
  post_max_size = 110M
  max_input_time = 300
  max_execution_time = 300
  ```
  PHP re-reads it after `user_ini.cache_ttl` (default 5 minutes).
- **`php.ini`** (own server): set the values in the php.ini used by the web server (e.g. `/etc/php/8.4/fpm/php.ini`, not the `cli` one), then restart PHP-FPM.
- **`.htaccess`** with `php_value upload_max_filesize 100M` etc. works **only** with Apache's `mod_php`. With PHP-FPM it causes a 500 error, so use `.user.ini` there instead.

Note that `php -i` on the command line shows the CLI settings, which can differ from the web server's. To check the real values, look in your hosting panel or upload a large video.

### Web server and proxies

- **nginx** allows only 1 MB request bodies by default. Add to the `server` block and reload nginx:
  ```nginx
  client_max_body_size 110M;
  ```
- **Apache** allows 1 GB by default (since 2.4.54). Only check this if `LimitRequestBody` was lowered by your host.
- **Reverse proxies / CDNs** in front of Taily have their own limits. For example, Cloudflare's free and pro plans cap uploads at 100 MB, so videos close to 100 MB can still fail there.

## Updating

```bash
composer update ulbrich-media/taily
```

This automatically publishes updated frontend assets and runs any new database migrations. Your `.env`, `storage/`, and published config overrides are never touched.

## Configuration

A `config/taily.php` file is published on first install. The available options are documented inline in that file.

## More Information

See the [Taily repository](https://github.com/ulbrich-media/taily) for the full project documentation, changelog, and issue tracker.