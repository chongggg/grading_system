FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libcurl4-openssl-dev \
    libgmp-dev \
    libonig-dev \
    libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip curl mbstring gmp \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock* ./
COPY app/composer.json ./app/

# Install PHP dependencies in root (for any root-level dependencies)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy all application files
COPY . /var/www/html/

# Install composer dependencies in app directory (where they're actually used)
RUN cd /var/www/html/app && \
    composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories and fix permissions
RUN mkdir -p runtime/session \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 runtime

EXPOSE 80
