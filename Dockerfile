## Stage 1: Composer untuk install dependencies
FROM composer:2 as composer

# Workdir aplikasi
WORKDIR /app

# Copy seluruh kode aplikasi terlebih dahulu
COPY . .

# Install dependencies aplikasi dengan composer
RUN composer install --ignore-platform-reqs --no-dev -a

## Stage 2: Main PHP Image dengan FrankenPHP
FROM dunglas/frankenphp:latest

# Install dependencies dengan cleanup agar image lebih kecil
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates curl ffmpeg \
    && install-php-extensions pcntl zip bcmath pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*
    
# Workdir aplikasi
WORKDIR /app

# Copy kode aplikasi dan hasil install dependencies dari stage sebelumnya
COPY --from=composer /app /app

# Install Octane dengan FrankenPHP tanpa interaksi
RUN echo "yes" | php artisan octane:install --server=frankenphp --no-interaction

# Set permission Laravel storage & cache dengan user www-data
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

# Konfigurasi PHP upload limit (gunakan COPY untuk file custom)
COPY custom-file.ini /usr/local/etc/php/conf.d/custom.ini

# Expose port 8000
EXPOSE 8010

# Jalankan Laravel Octane dengan FrankenPHP
ENTRYPOINT ["php", "artisan", "octane:frankenphp"]
CMD ["--host=0.0.0.0", "--port=8010"]
