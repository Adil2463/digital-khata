# Deploying To A Single Server

`hosting/` holds a ready-to-use nginx setup for running the whole kit on one Linux host: PHP-FPM serves the application, Reverb shares the same domain for websockets, and PM2 (`pm2.config.cjs`) runs the queue workers, Reverb, the Inertia SSR server, and the scheduler.

The configs are written for Ubuntu 24.04 (nginx 1.24, PHP-FPM from `ondrej/php`) with the checkout at `/srv/laravel-starter-kit`. Other Debian-based hosts need only the adjustments listed in [Values to adjust](#values-to-adjust).

## Contents

| File                                               | Purpose                                                                                |
| -------------------------------------------------- | -------------------------------------------------------------------------------------- |
| `hosting/nginx_config/app.conf`                    | Site on port `8001` for use behind a proxy that terminates TLS.                        |
| `hosting/nginx_config/app-https.conf`              | Site on ports `80`/`443` with nginx terminating TLS using a Let's Encrypt certificate. |
| `hosting/nginx_config/snippets/app-locations.conf` | Routing shared by both sites. Only include it from a site file.                        |
| `hosting/nginx_logs/`                              | nginx access and error logs. The directory is committed; the logs are ignored.         |
| `hosting/logrotate/laravel-starter-kit`            | Rotation policy for those logs.                                                        |

Use exactly one of the two site files.

## Choose A Topology

HTTPS is required. Inertia history encryption (`INERTIA_ENCRYPT_HISTORY=true`) needs a secure context, which browsers only grant to HTTPS and `localhost`.

- **Behind a TLS-terminating proxy** (`app.conf`): Cloudflare Tunnel, a cloud load balancer, Coolify/Caddy, or another nginx. The proxy must forward `X-Forwarded-For`, `X-Forwarded-Proto`, and `X-Forwarded-Host` and allow websocket upgrades. Set `TRUSTED_PROXIES` to the proxy's address, or Laravel generates `http://` URLs and the browser blocks the page's assets as mixed content. Block port `8001` from everything except the proxy.
- **nginx terminates TLS** (`app-https.conf`): nginx is the public edge. Leave `TRUSTED_PROXIES` unset so forwarded headers sent by clients are ignored.

## 1. Prepare The Server

Install nginx, PHP 8.4 with FPM and the extensions your database needs, Composer, Node 24.15+ with npm 11.2.1+, and PM2 (`npm install -g pm2`). The frontend toolchain requires glibc; Alpine/musl hosts are unsupported.

Clone the repository into `/srv/laravel-starter-kit` as the deploy user, and let PHP-FPM's group write the runtime directories. PM2 starts every process with `umask 0002`, so files they create stay group-writable:

```bash
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R g+rwX storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod g+s {} +
```

## 2. Configure The Environment

Copy `.env.example` to `.env`, run `php artisan key:generate`, and set at least the following, with `example.com` replaced by your domain:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
LOG_LEVEL=warning

# The users migration otherwise creates admin@app.com and user@app.com with
# the passwords published in the migration. Set this before the first migrate.
APP_SEED_USERS=false

# Behind a TLS-terminating proxy only; see "Choose a topology".
TRUSTED_PROXIES=127.0.0.1

SESSION_SECURE_COOKIE=true

REVERB_APP_KEY=<random>
REVERB_APP_SECRET=<random>
REVERB_APP_ID=<random>
REVERB_HOST=example.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080
```

- `REVERB_HOST`, `REVERB_PORT`, and `REVERB_SCHEME` are the public address: browsers open `wss://example.com/app/{key}`, and the queue workers publish to `https://example.com/apps/{id}/events`. nginx forwards both to Reverb, so the application key can be any value.
- `REVERB_SERVER_HOST=127.0.0.1` keeps Reverb reachable only through nginx. PM2 starts it without flags, so these values are read from `.env`.
- The `VITE_*` variables are compiled into the frontend. Rebuild the assets whenever a Reverb or app-name value changes.
- `SANCTUM_STATEFUL_DOMAINS` defaults to the `APP_URL` host. Set it explicitly only when the frontend is served from additional hosts.
- Use a real database (`DB_CONNECTION`, `DB_*`) and mail transport (`MAIL_*`) for production.

## 3. Build And Migrate

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build:ssr
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Keep `node_modules` after the build: the SSR bundle in `bootstrap/ssr` imports its dependencies at runtime. Make sure `public/hot` does not exist in production; it points pages at a Vite dev server.

## 4. Enable The Site

```bash
# Behind a TLS proxy
sudo ln -s /srv/laravel-starter-kit/hosting/nginx_config/app.conf /etc/nginx/sites-enabled/laravel-starter-kit.conf

# Or nginx terminating TLS: replace example.com in app-https.conf, then issue
# the certificate before enabling the site (nginx refuses to start without it).
sudo certbot certonly --nginx -d example.com
sudo ln -s /srv/laravel-starter-kit/hosting/nginx_config/app-https.conf /etc/nginx/sites-enabled/laravel-starter-kit.conf

sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Certbot's renewal timer reuses the nginx authenticator, so renewals need no further setup.

## 5. Rotate Logs

The logs live in the checkout, outside the system logrotate policy for `/var/log/nginx`:

```bash
sudo install -m 0644 hosting/logrotate/laravel-starter-kit /etc/logrotate.d/laravel-starter-kit
sudo logrotate --debug /etc/logrotate.d/laravel-starter-kit
```

## 6. Start The Processes

```bash
pm2 start pm2.config.cjs
pm2 save
pm2 startup   # run the command it prints so PM2 starts on boot
```

Run the scheduler on one host only.

## Deploying Updates

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build:ssr
php artisan migrate --force
php artisan optimize
php artisan up
php artisan queue:restart
php artisan reverb:restart
pm2 restart inertia-ssr
```

Services that exit after `queue:restart` or `reverb:restart` are restarted by PM2. Reload PHP-FPM (`sudo systemctl reload php8.4-fpm`) when OPcache is configured without timestamp validation. The site uses `$realpath_root`, so symlink-swapping release directories works without an nginx reload.

## Values To Adjust

| Value                           | Where                                      | Change it when                                                                                    |
| ------------------------------- | ------------------------------------------ | ------------------------------------------------------------------------------------------------- |
| `/srv/laravel-starter-kit`      | all three nginx files, the logrotate file  | the checkout lives elsewhere                                                                      |
| `unix:/run/php/php8.4-fpm.sock` | `snippets/app-locations.conf`              | you run PHP 8.5 or another FPM pool                                                               |
| `127.0.0.1:8080`                | `snippets/app-locations.conf` (two places) | `REVERB_SERVER_PORT` is not `8080`                                                                |
| `8001`                          | `app.conf`                                 | your proxy forwards to another port                                                               |
| `example.com`                   | `app-https.conf`                           | always, when using that file                                                                      |
| `client_max_body_size 66m`      | `snippets/app-locations.conf`              | uploads need a different limit; PHP's `upload_max_filesize` and `post_max_size` must allow it too |

## What The nginx Setup Handles

- **Reverb on the same domain.** Reverb's websocket endpoint is `/app/{key}`, which shares its prefix with the application's `/app/*` pages. Only requests carrying `Upgrade: websocket` reach Reverb, so pages such as `/app/dashboard` keep working and the application key is never written into nginx. `/apps/*` is Reverb's HTTP API; its requests are signed with `REVERB_APP_SECRET`.
- **Front controller only.** `public/index.php` is the only PHP file that runs. Any other `.php` request returns Laravel's 404, so a PHP file that ends up under `public/` is neither executed nor served.
- **Uploads.** Files under `/storage/` are served with a sandboxing Content Security Policy so an uploaded HTML or SVG file cannot run script on the application's origin. Missing files fall through to Laravel's `local` disk route.
- **Caching and compression.** Content-hashed Vite output under `/build/assets/` is cached for a year as immutable, other static files for a week, and text assets are gzip-compressed.
- **Hidden files.** Dotfiles such as `.env` are denied except `/.well-known/`.
- **Security headers.** Application responses carry the headers set by `App\Http\Middleware\SecurityHeaders`; nginx adds HSTS in `app-https.conf` only.

## Troubleshooting

| Symptom                                                       | Cause                                                                                                                           |
| ------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| Page loads unstyled and the console reports mixed content     | Behind a proxy without `TRUSTED_PROXIES`, or the proxy does not send `X-Forwarded-Proto: https`.                                |
| Every visit fails with a history-encryption or `crypto` error | The site is served over plain HTTP on a non-localhost host. Serve it over HTTPS.                                                |
| Websocket connects to `ws://…:8080` or to the wrong host      | `VITE_REVERB_*` held development values at build time. Fix `.env` and rebuild the assets.                                       |
| Websocket upgrade returns 502                                 | Reverb is not running, or `REVERB_SERVER_PORT` differs from the port in `snippets/app-locations.conf`. Check `pm2 logs reverb`. |
| Broadcasts never arrive but the socket connects               | Queue workers are not running, or they cannot reach `https://REVERB_HOST/apps/…`. Check `pm2 logs queue-workers`.               |
| `nginx -t` reports a missing log file                         | `hosting/nginx_logs/` was removed. Restore it with `git checkout -- hosting/nginx_logs`.                                        |
| 502 on every page                                             | PHP-FPM is stopped or listens on a different socket than `fastcgi_pass`.                                                        |
| 419 or unauthenticated API calls from the SPA                 | `APP_URL` or `SANCTUM_STATEFUL_DOMAINS` does not match the host in the browser.                                                 |
