FROM php:8.2-apache

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

# FIX MPM - Remove all MPM modules first
RUN a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true

# Enable only mpm_prefork
RUN a2enmod mpm_prefork

# Enable other Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction || \
    (rm -f composer.lock && composer install --no-dev --optimize-autoloader --no-interaction)

# Copy all application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Configure Apache for Railway port
ENV PORT=8080
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf && \
    sed -i "s/:80/:8080/" /etc/apache2/sites-available/000-default.conf

# Configure document root
RUN echo '<Directory /var/www/html/>' >> /etc/apache2/apache2.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 8080

# Start Apache in foreground
CMD ["apache2-foreground"]
