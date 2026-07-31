#!/bin/sh
set -e

# Wait for DB connection if host is set
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Checking database availability on $DB_HOST:$DB_PORT..."
    php -r '
        $host = getenv("DB_HOST");
        $port = getenv("DB_PORT") ?: 3306;
        $db   = getenv("DB_DATABASE");
        $user = getenv("DB_USERNAME");
        $pass = getenv("DB_PASSWORD");
        $attempts = 0;
        while ($attempts < 30) {
            try {
                $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
                echo "Database connection successful!\n";
                exit(0);
            } catch (PDOException $e) {
                echo "Database not ready yet, retrying in 2 seconds... (" . ($attempts+1) . "/30)\n";
                sleep(2);
                $attempts++;
            }
        }
        echo "Could not connect to database. Exiting.\n";
        exit(1);
    '
fi

# System and framework storage directories
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/app
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/testing
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/fonts
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/log/supervisor
mkdir -p /var/www/html/public/images

# Auto-create all PUBLIC upload subfolders for all modules (Portal, WorkOS, PAGI, WIMS, FAST, Tracer)
mkdir -p /var/www/html/storage/app/public/portal/gallery
mkdir -p /var/www/html/storage/app/public/portal/partners
mkdir -p /var/www/html/storage/app/public/portal/posts/thumbnails
mkdir -p /var/www/html/storage/app/public/portal/posts/seo
mkdir -p /var/www/html/storage/app/public/portal/posts/content
mkdir -p /var/www/html/storage/app/public/portal/events
mkdir -p /var/www/html/storage/app/public/portal/media
mkdir -p /var/www/html/storage/app/public/portal/author
mkdir -p /var/www/html/storage/app/public/portal/categories
mkdir -p /var/www/html/storage/app/public/portal/academic_calendars
mkdir -p /var/www/html/storage/app/public/portal/modules
mkdir -p /var/www/html/storage/app/public/branding
mkdir -p /var/www/html/storage/app/public/profile_photos
mkdir -p /var/www/html/storage/app/public/cv-photos
mkdir -p /var/www/html/storage/app/public/avatars
mkdir -p /var/www/html/storage/app/public/pagi/works
mkdir -p /var/www/html/storage/app/public/pagi/covers
mkdir -p /var/www/html/storage/app/public/pagi/gallery
mkdir -p /var/www/html/storage/app/public/pagi/banners
mkdir -p /var/www/html/storage/app/public/pagi/certificates
mkdir -p /var/www/html/storage/app/public/pagi/certificates/thumbs
mkdir -p /var/www/html/storage/app/public/org-logos
mkdir -p /var/www/html/storage/app/public/tracer/events
mkdir -p /var/www/html/storage/app/public/tracer/jobs
mkdir -p /var/www/html/storage/app/public/wims/proposals
mkdir -p /var/www/html/storage/app/public/wims/reports
mkdir -p /var/www/html/storage/app/public/logbook
mkdir -p /var/www/html/storage/app/public/ketidakhadiran
mkdir -p /var/www/html/storage/app/public/absensi
mkdir -p /var/www/html/storage/app/public/proposals
mkdir -p /var/www/html/storage/app/public/laporan_akhir
mkdir -p /var/www/html/storage/app/public/fast/submissions

# PRIVATE storage directories (Laravel 12 default local disk root storage/app/private & legacy storage/app)
mkdir -p /var/www/html/storage/app/private/portal/documents
mkdir -p /var/www/html/storage/app/private/wims/proposals
mkdir -p /var/www/html/storage/app/private/wims/reports
mkdir -p /var/www/html/storage/app/private/wims/templates
mkdir -p /var/www/html/storage/app/private/logbook
mkdir -p /var/www/html/storage/app/private/ketidakhadiran
mkdir -p /var/www/html/storage/app/private/absensi
mkdir -p /var/www/html/storage/app/private/proposals
mkdir -p /var/www/html/storage/app/private/laporan_akhir
mkdir -p /var/www/html/storage/app/private/fast/template
mkdir -p /var/www/html/storage/app/private/surat-lampirans
mkdir -p /var/www/html/storage/app/private/trace/alumni-role-change-proofs
mkdir -p /var/www/html/storage/app/portal/documents
mkdir -p /var/www/html/storage/app/wims/proposals
mkdir -p /var/www/html/storage/app/wims/reports
mkdir -p /var/www/html/storage/app/wims/templates
mkdir -p /var/www/html/storage/app/logbook
mkdir -p /var/www/html/storage/app/ketidakhadiran
mkdir -p /var/www/html/storage/app/absensi
mkdir -p /var/www/html/storage/app/proposals
mkdir -p /var/www/html/storage/app/laporan_akhir
mkdir -p /var/www/html/storage/app/fast/template
mkdir -p /var/www/html/storage/app/surat-lampirans
mkdir -p /var/www/html/storage/app/trace/alumni-role-change-proofs

echo "Setting storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/images
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/images

echo "Creating storage symlink..."
php artisan storage:link --force || true

# Caching Laravel configuration, routes, and views for production optimization
echo "Caching Laravel assets and configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run database migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Register Laravel scheduler cron job
echo "Configuring Laravel scheduler cron job..."
echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root

echo "Starting Supervisor process manager..."
exec "$@"
