FROM php:8.2-apache

# Instalar extensiones de MySQL y SQLite (usada por el sistema de reservas)
RUN docker-php-ext-install mysqli pdo pdo_mysql pdo_sqlite

# Habilitar mod_rewrite, necesario para el enrutamiento de la aplicación PHP
RUN a2enmod rewrite

# Copiar archivos del proyecto
COPY . /var/www/html/

# Desactivar la adición de puertos internos en redirecciones automáticas de Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalName Off" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalPhysicalPort Off" >> /etc/apache2/apache2.conf

# Configurar Apache para escuchar en el puerto indicado por la variable $PORT de Railway
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
 && sed -i 's/:80>/:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

ENV PORT=80
EXPOSE $PORT

CMD ["apache2-foreground"]