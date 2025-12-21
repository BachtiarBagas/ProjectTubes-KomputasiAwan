# Menggunakan image PHP dengan Apache
FROM php:8.1-apache

# Install ekstensi yang biasanya dibutuhkan PHP (opsional)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy semua file proyek ke folder web Apache di dalam container
COPY . /var/www/html/

# Berikan izin akses folder
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80