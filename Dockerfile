# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

COPY . .

# ---------- Stage 2: PHP + Apache ----------
FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

RUN a2enmod rewrite

RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Options Indexes FollowSymLinks\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog /var/log/apache2/error.log\n\
    CustomLog /var/log/apache2/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf \
    && echo "<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>" >> /etc/apache2/apache2.conf

COPY --from=build /app /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

WORKDIR /var/www/html

# Health Route
RUN printf "\nRoute::get('/health', function () { return response()->json(['status' => 'ok']); });\n" >> routes/web.php

# Show Routes (اختياري)
RUN echo "=== ROUTE LIST START ===" && php artisan route:list && echo "=== ROUTE LIST END ==="

# ---------- ⛔️ لا يوجد أي Migrate أو Fresh أو Seed ----------
CMD apache2-foreground

ENV PORT=8080
EXPOSE 8080
