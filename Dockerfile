FROM php:8.2-apache

# Install PostgreSQL C-library dependency and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Expose internal HTTP port
EXPOSE 80

# Define runtime environment fallbacks #Compose will overwrite this
ENV PGHOST=restaurant-db \
    PGDATABASE=restaurant_db \
    PGUSER=chef_admin \
    PGPASSWORD=kitchen_secure_pass \
    PGPORT=5432

# Copy web source code
COPY ./src /var/www/html

# Grant ownership to Apache's runtime user
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html