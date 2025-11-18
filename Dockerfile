FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first (for better caching)
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Copy application files
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Set permissions for storage and bootstrap/cache
RUN chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 8000

# Start PHP built-in server (Render sets PORT env var)
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
