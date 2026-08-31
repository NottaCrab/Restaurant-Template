FROM php:8.2-apache

# Instalar extensiones de MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos
COPY . /var/www/html/

# 1. Desactivar la inclusión del puerto en las redirecciones relativas
RUN echo "UseCanonicalName Off" >> /etc/apache2/apache2.conf
RUN echo "UseCanonicalPhysicalPort Off" >> /etc/apache2/apache2.conf

# 2. Configurar el puerto dinámico de Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80