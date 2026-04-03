#!/bin/sh
set -e

echo "Creating database (if not exists)..."
php bin/console doctrine:database:create --if-not-exists --no-interaction

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Loading fixtures..."
php bin/console sylius:fixtures:load --no-interaction

echo "Clearing and warming cache..."
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

echo "Installing assets..."
php bin/console assets:install public

exec "$@"
