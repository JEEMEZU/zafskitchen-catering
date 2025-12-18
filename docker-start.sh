#!/bin/bash
set -e

echo "🚀 Starting Zaf's Kitchen Application..."

if [ -z "$DATABASE_URL" ]; then
    echo "❌ ERROR: DATABASE_URL not set"
    exit 1
fi

echo "✅ DATABASE_URL configured"

# Check PostgreSQL
php -m | grep -i pdo_pgsql > /dev/null || exit 1
echo "✅ PostgreSQL PDO loaded"

# Install composer dependencies if needed
if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader
fi

echo "✅ Application ready"
echo "🌐 Starting PHP server on port ${PORT:-8080}"

# Start with router
cd /var/www/html
exec php -S 0.0.0.0:${PORT:-8080} router.php