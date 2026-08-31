FROM php:8.2-apache

# Copia los archivos del repositorio a la carpeta pública del servidor Apache
COPY . /var/www/html/

# Configura Apache para escuchar en el puerto que asigna Render ($PORT)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80