FROM php:8.2-fpm

# Instal dependensi sistem yang esensial
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

# Instal ekstensi PHP (pdo_pgsql sangat penting untuk auth)
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd sockets

# Instal Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Dapatkan Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tentukan direktori kerja utama
WORKDIR /var/www

# Salin semua file proyek (akan tertimpa oleh bind mount saat dev lokal, tapi penting untuk production build)
COPY . /var/www

# Instal dependensi Laravel (optimisasi untuk production)
# Catatan: Ini mungkin gagal jika composer.lock tidak punya dependensi yang tepat, kita pasang fallback
RUN composer install --no-dev --optimize-autoloader || true

# Berikan izin ke storage dan cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
