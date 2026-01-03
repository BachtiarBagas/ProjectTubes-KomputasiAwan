FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Install dependencies
RUN apk add --no-cache nginx supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD [""php-fpm"", "index.php"]
