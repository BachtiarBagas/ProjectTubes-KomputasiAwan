FROM php:8.2-cli-alpine

WORKDIR /var/www/html

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 5000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:5000", "-t", "/var/www/html"]
