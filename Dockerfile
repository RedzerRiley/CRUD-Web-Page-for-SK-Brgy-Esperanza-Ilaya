FROM php:8.2-apache

RUN a2enmod rewrite

# Disable all MPM modules except prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

COPY . /var/www/html/

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]