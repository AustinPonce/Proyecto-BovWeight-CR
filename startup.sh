#!/bin/bash

# Apuntar Apache al directorio public/ de Laravel
sed -i 's|/home/site/wwwroot|/home/site/wwwroot/public|g' /etc/apache2/sites-enabled/000-default.conf

# Habilitar mod_rewrite para .htaccess
a2enmod rewrite

# Escribir certificado Aiven si está disponible como variable de entorno
if [ -n "$AIVEN_CA_CERT" ]; then
    mkdir -p /home/site/wwwroot/storage/certs
    echo "$AIVEN_CA_CERT" > /home/site/wwwroot/storage/certs/ca-certificate.pem
fi

# Comandos de Laravel
cd /home/site/wwwroot
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan storage:link

# Reiniciar Apache con la nueva configuración
apachectl restart
