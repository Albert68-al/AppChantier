FROM php:8.2-apache

RUN a2enmod rewrite \
 && docker-php-ext-install pdo pdo_sqlite

# Apache: autoriser .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html/

# Dossier SQLite
RUN mkdir -p /var/www/html/data \
 && chown -R www-data:www-data /var/www/html/data

# Render fournit PORT (souvent 10000). Apache écoute par défaut sur 80.
# On remplace le port au démarrage puis on lance Apache.
CMD ["bash", "-lc", "sed -i 's/Listen 80/Listen '"\$PORT""'/g' /etc/apache2/ports.conf && sed -i 's/<VirtualHost \\*:80>/<VirtualHost *:'"\$PORT"'>/g' /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
