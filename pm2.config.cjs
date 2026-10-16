module.exports = {
    // Every app is launched through `bash -c` so the umask is applied before PHP
    // starts; the real command therefore lives in `interpreter_args`, and
    // `script` is only the label pm2 reports.
    apps: [
        {
            instances: 4,
            name: 'queue-workers',
            script: 'artisan',
            interpreter_args: '-c "umask 0002 && exec php artisan queue:work --queue=realtime,high,default --sleep=3 --tries=2 --timeout=60"',
            cwd: './',
            error_file: './storage/logs/pm2/queue-workers/pm2-error.log',
            out_file: './storage/logs/pm2/queue-workers/pm2-out.log',
            cron_restart: '0 0 * * *',
            restart_delay: 1000,
            autorestart: true,
            merge_logs: true,
            watch: false,
            exec_mode: 'fork',
            interpreter: '/bin/bash',
            max_memory_restart: '512M',
            env: {
                NODE_ENV: 'production'
            }
        },
        {
            instances: 1,
            name: 'reverb',
            script: 'artisan',
            // Without flags, reverb:start binds to REVERB_SERVER_HOST/PORT from `.env`.
            interpreter_args: '-c "umask 0002 && exec php artisan reverb:start --no-interaction"',
            cwd: './',
            error_file: './storage/logs/pm2/reverb/pm2-error.log',
            out_file: './storage/logs/pm2/reverb/pm2-out.log',
            cron_restart: '0 0 * * *',
            restart_delay: 1000,
            autorestart: true,
            merge_logs: true,
            watch: false,
            exec_mode: 'fork',
            interpreter: '/bin/bash',
            max_memory_restart: '512M',
            env: {
                NODE_ENV: 'production'
            }
        },
        {
            instances: 1,
            name: 'inertia-ssr',
            script: 'artisan',
            interpreter_args: '-c "umask 0002 && exec php artisan inertia:start-ssr"',
            cwd: './',
            error_file: './storage/logs/pm2/inertia-ssr/pm2-error.log',
            out_file: './storage/logs/pm2/inertia-ssr/pm2-out.log',
            cron_restart: '0 0 * * *',
            restart_delay: 1000,
            autorestart: true,
            merge_logs: true,
            watch: false,
            exec_mode: 'fork',
            interpreter: '/bin/bash',
            max_memory_restart: '512M',
            env: {
                NODE_ENV: 'production'
            }
        },
        {
            instances: 1,
            name: 'scheduler',
            script: 'artisan',
            interpreter_args: '-c "umask 0002 && exec php artisan schedule:work"',
            cwd: './',
            error_file: './storage/logs/pm2/scheduler/pm2-error.log',
            out_file: './storage/logs/pm2/scheduler/pm2-out.log',
            cron_restart: '0 0 * * *',
            restart_delay: 1000,
            autorestart: true,
            merge_logs: true,
            watch: false,
            exec_mode: 'fork',
            interpreter: '/bin/bash',
            max_memory_restart: '512M',
            env: {
                NODE_ENV: 'production'
            }
        }
    ]
}
