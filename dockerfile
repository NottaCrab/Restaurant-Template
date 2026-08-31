FROM php:8.2-apache

# Instalar extensiones de MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Desactivar la adición de puertos internos en redirecciones automáticas de Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalName Off" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalPhysicalPort Off" >> /etc/apache2/apache2.conf

# Reemplazar el puerto 80 por la variable $PORT de Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80