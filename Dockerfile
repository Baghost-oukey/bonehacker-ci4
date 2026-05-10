FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    git \
    curl \
    gnupg \
    libfreetype6-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl gd zip mysqli pdo pdo_mysql mbstring

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache Document Root to CodeIgniter 4 /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js for Vite build
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Set Working Directory
WORKDIR /var/www/html

# Copy Project Files
COPY . .

# Install PHP & Node Dependencies
# Note: We run these during build for a production-ready image
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Set permissions for CI4 writable directory
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public

EXPOSE 80

CMD ["apache2-foreground"]
