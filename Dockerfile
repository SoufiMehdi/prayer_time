# Étape 1 : Build des assets avec Node.js
FROM node:18-alpine AS node_builder
WORKDIR /app

# Copier les fichiers de dépendances
COPY package*.json ./
COPY yarn.lock* ./

# Installer les dépendances Node
RUN npm install || yarn install

# Copier le reste des fichiers
COPY . .

# Build des assets Symfony UX
RUN npm run build || yarn build

# Étape 2 : Image PHP avec Symfony et PostgreSQL
FROM php:8.2-fpm-alpine

# Installer les dépendances système
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    nginx \
    supervisor \
    oniguruma-dev \
    postgresql-dev \
    bash \
    curl

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install \
    intl \
    zip \
    opcache \
    mbstring \
    pdo_pgsql \
    pgsql \
    pcntl

# Configuration PHP optimisée
RUN echo "date.timezone = Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini && \
    echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory.ini && \
    echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Augmenter la limite mémoire PHP pour Composer
ENV COMPOSER_MEMORY_LIMIT=-1

# Créer le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de configuration de Composer
COPY composer.json composer.lock* symfony.lock* ./

# Installer les dépendances Symfony
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist || \
    composer install --no-dev --no-scripts --no-autoloader --no-interaction --ignore-platform-reqs --prefer-dist

# Copier le reste des fichiers Symfony
COPY . .

# Copier les assets buildés depuis l'étape Node
COPY --from=node_builder /app/public/build ./public/build

# Générer l'autoloader optimisé
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Créer tous les répertoires nécessaires
RUN mkdir -p \
    /var/www/html/var/cache/prod \
    /var/www/html/var/log \
    /var/www/html/var/sessions \
    /var/lib/nginx/tmp \
    /var/lib/nginx/logs \
    /var/log/nginx \
    /var/log/supervisor \
    /run/nginx

# Créer les fichiers de log
RUN touch \
    /var/www/html/var/log/messenger.log \
    /var/www/html/var/log/messenger_error.log \
    /var/www/html/var/log/scheduler.log \
    /var/www/html/var/log/scheduler_error.log

# Configuration Nginx
RUN cat > /etc/nginx/http.d/default.conf <<'EOF'
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
}
EOF

# Copier le fichier supervisord.conf
COPY Docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ajuster les permissions
RUN chown -R www-data:www-data \
    /var/www/html/var \
    /var/www/html/public \
    /var/lib/nginx \
    /var/log/nginx \
    /var/log/supervisor \
    /run/nginx && \
    chmod -R 775 /var/www/html/var

# Variables d'environnement
ENV APP_ENV=prod
ENV APP_DEBUG=0

EXPOSE 80

# Lancer supervisor en tant que root
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
