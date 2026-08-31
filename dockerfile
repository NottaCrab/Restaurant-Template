FROM php:8.2-apache

# Instalar extensiones de MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Desactivar la adición de puertos internos en redirecciones automáticas de Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalName Off" >> /etc/apache2/apache2.conf \
 && echo "UseCanonicalPhysicalPort Off" >> /etc/apache2/apache2.conf

# Configurar Apache para escuchar en el puerto definido por la variable de entorno PORT
# (Railway la asigna automáticamente; se usa 8080 por defecto). Apache soporta
# interpolación de variables de entorno con la sintaxis ${VAR} en sus archivos de
# configuración, por lo que el valor real se resuelve en tiempo de ejecución.
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

ENV PORT=8080
EXPOSE 8080

CMD ["apache2-foreground"]