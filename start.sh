#!/bin/bash
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
exec apache2-foreground