#!/bin/sh
set -e

echo "Deploying application ..."

# Enter maintenance mode
(php artisan down --secret="bypass") || true
    # Update codebase
    git fetch origin dev-deploy
    git reset --hard origin/dev-deploy

    # Install dependencies based on lock file
    composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

    # Migrate database
    php artisan migrate --force

    # Clear cache
    php artisan optimize:clear

# Exit maintenance mode
php artisan up

echo "Application deployed!"
