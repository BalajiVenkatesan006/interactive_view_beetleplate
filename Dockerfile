# Use the official PHP image with Apache
FROM php:7.4-apache

# Enable necessary Apache mods
RUN a2enmod rewrite

# Set the working directory to /var/www/html
WORKDIR /var/www/html

# Copy the PHP source files into the container
COPY . /var/www/html/

# Install necessary PHP extensions if required
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Ensure the 'data' directory and its subdirectories are writable by the web server
RUN chown -R www-data:www-data /var/www/html/data && chmod -R 755 /var/www/html/data

# Configure Apache to listen to the PORT environment variable
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf

# Expose the port that Cloud Run will bind to
EXPOSE 8080

# Set the PORT environment variable using the correct key=value format
ENV PORT=8080

# Set the default command to run Apache
CMD ["apache2-foreground"]
