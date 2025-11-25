#!/usr/bin/env bash
# Render build script

set -o errexit

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Copying vendor to app directory..."
if [ -d vendor ] && [ ! -d app/vendor ]; then
    cp -r vendor app/vendor
fi

echo "Creating runtime directories..."
mkdir -p runtime/session
chmod -R 777 runtime

echo "Build completed successfully!"
