FROM php:8.2-apache

RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

WORKDIR /var/www/html

# Startup script to set port dynamically
CMD bash -c "sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf && sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf && apache2-foreground"