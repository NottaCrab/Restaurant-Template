FROM php:8.2-apache

# Instalar extensiones comunes de PHP para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos
COPY . /var/www/html/

# Configurar Apache para que escuche en el puerto de Render ($PORT)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80