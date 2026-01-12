FROM php:8.2-apache

# Activer rewrite + SQLite
RUN a2enmod rewrite \
    && apt-get update \
    && apt-get install -y sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Autoriser .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Dossier de travail
WORKDIR /var/www/html

# Copier le projet
COPY . /var/www/html/

# Dossier SQLite + permissions
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Adapter Apache au port Render
CMD ["bash", "-lc", "sed -i \"s/Listen 80/Listen ${PORT}/g\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/g\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
