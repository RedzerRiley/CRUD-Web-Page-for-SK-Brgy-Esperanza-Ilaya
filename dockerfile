FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files into container
COPY . /var/www/html/

# Set working directorys
WORKDIR /var/www/html

EXPOSE 80