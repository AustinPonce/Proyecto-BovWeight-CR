#!/bin/bash

# Apuntar Apache al directorio public/ de Laravel
cat > /etc/apache2/sites-available/000-default.conf << 'EOF'
<VirtualHost *:8080>
    DocumentRoot /home/site/wwwroot/public
    <Directory /home/site/wwwroot/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /home/LogFiles/apache_error.log
    CustomLog /home/LogFiles/apache_access.log combined
</VirtualHost>
EOF

a2enmod rewrite

cd /home/site/wwwroot

# Escribir certificado Aiven si existe el secret
if [ -n "$AIVEN_CA_CERT" ]; then
    mkdir -p storage/certs
    echo "$AIVEN_CA_CERT" > storage/certs/ca-certificate.pem
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan storage:link

apachectl -D FOREGROUND
