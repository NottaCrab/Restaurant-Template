FROM php:8.2-apache

# Instalar dependencias del sistema necesarias para compilar el soporte de SQLite
RUN apt-get update && apt-get install -y --no-install-recommends \
        libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones de MySQL y SQLite (usada por el sistema de reservas)
# pdo_sqlite requiere ser configurado explícitamente antes de instalarse
RUN docker-php-ext-configure pdo_sqlite \
 && docker-php-ext-install mysqli pdo pdo_mysql pdo_sqlite

# Habilitar mod_rewrite, necesario para el enrutamiento de la aplicación PHP
RUN a2enmod rewrite

# Copiar archivos del proyecto
COPY . /var/www/html/

# Configurar DirectoryIndex para que Apache sirva index.php automáticamente
# en cualquier directorio (por ejemplo, /reservas/), con index.html como respaldo
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/directory-index.conf \
 && a2enconf directory-index

# Desactivar la adición de puertos internos en redirecciones automáticas de Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalName Off" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalPhysicalPort Off" >> /etc/apache2/apache2.conf

# Script de entrada que configura el puerto en tiempo de ejecución usando la
# variable $PORT que Railway inyecta al iniciar el contenedor
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENV PORT=80
EXPOSE $PORT

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]