FROM php:8.2-apache

RUN a2enmod rewrite

COPY . /var/www/html/

WORKDIR /var/www/html

# Copy and run startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
