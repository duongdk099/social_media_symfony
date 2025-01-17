FROM php:8.2-fpm

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_mysql

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Installer les dépendances
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copier le reste du projet
COPY . .

# Finaliser l'installation de Composer
RUN composer dump-autoload --optimize
