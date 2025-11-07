# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app
COPY composer.json composer.lock ./
# Don't run package scripts during install (prevents package discovery errors at build-time)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts
COPY . .

# ---------- Stage 2: PHP + Apache ----------
FROM php:8.2-apache

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy app from build stage
COPY --from=build /app /var/www/html

# Ensure database file exists (for sqlite) — safe if already present
RUN mkdir -p /var/www/html/database && touch /var/www/html/database/database.sqlite

# Permissions for storage & cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Enable mod_rewrite and ensure DocumentRoot points to public
RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Allow .htaccess overrides inside public
RUN printf "<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Clear caches (in case) and generate key if missing
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan cache:clear || true
RUN php artisan key:generate --ansi || true

# Run migrations on startup then start Apache
CMD php artisan migrate --force && apache2-foreground

ENV PORT=8080
EXPOSE 8080
