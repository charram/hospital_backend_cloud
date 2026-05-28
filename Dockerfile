FROM php:8.2-apache-bookworm

# Install PostgreSQL extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Enable rewrite
RUN a2enmod rewrite

# Copy project
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

EXPOSE 80