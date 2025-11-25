#!/usr/bin/env bash
# Render build script

set -o errexit

echo "Installing root Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing app Composer dependencies..."
cd app
composer install --no-dev --optimize-autoloader
cd ..

echo "Creating runtime directories..."
mkdir -p runtime/session
chmod -R 777 runtime

echo "Build completed successfully!"
