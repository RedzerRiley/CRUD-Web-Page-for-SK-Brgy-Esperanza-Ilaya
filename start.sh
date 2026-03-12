#!/bin/bash

# Write fresh Apache config with correct port
cat > /etc/apache2/ports.conf << EOF
Listen $PORT
EOF

cat > /etc/apache2/sites-available/000-default.conf << EOF
<VirtualHost *:$PORT>
    DocumentRoot /var/www/html
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

exec apache2-foreground