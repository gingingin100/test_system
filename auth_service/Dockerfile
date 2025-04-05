FROM php:8.0-apache

# Install required dependencies for mysqli and curl
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install mysqli curl \
    && docker-php-ext-enable mysqli curl

# Copy your application files into the container
COPY . /var/www/html/

RUN pecl install -o -f redis; rm -rf /tmp/pear; docker-php-ext-enable redis;

# Expose port 80
EXPOSE 80

# Enable Apache mod_rewrite
RUN a2enmod rewrite