FROM php:8.2-cli

# Install dependencies
RUN apt-get update && \
    apt-get install -y \
        libpq-dev \
        libzip-dev \
        zip \
        unzip \
        git \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy files
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction || \
    (rm -f composer.lock && composer install --no-dev --optimize-autoloader --no-interaction)

COPY . .

# Make startup script executable
RUN chmod +x docker-start.sh

# Set permissions
RUN chmod -R 755 /var/www/html

ENV PORT=8080
EXPOSE 8080

CMD ["./docker-start.sh"]
