FROM php:8.2-apache

# ARG ต้องอยู่ติดกับ RUN ที่ใช้มัน เพื่อ bust cache จริงๆ
ARG CACHE_BUST=3
RUN echo "Cache bust: $CACHE_BUST" \
    && a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true \
    && a2enmod mpm_prefork

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY . /var/www/html/

WORKDIR /var/www/html

EXPOSE 80