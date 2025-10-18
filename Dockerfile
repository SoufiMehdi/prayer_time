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


# Étape 2 : Image PHP avec Symfony
FROM php:8.2-fpm-alpine

# Installer les dépendances système
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    nginx \
    supervisor \
    oniguruma-dev

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install \
    intl \
    zip \
    opcache \
    mbstring

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Augmenter la limite mémoire PHP pour Composer
ENV COMPOSER_MEMORY_LIMIT=-1

# Créer le répertoire de travail
WORKDIR /var/www/html

# Copier d'abord composer.json et composer.lock pour utiliser le cache Docker
COPY composer.json composer.lock* symfony.lock* ./

# Installer les dépendances Symfony (sans scripts et autoload pour l'instant)
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist || \
    composer install --no-dev --no-scripts --no-autoloader --no-interaction --ignore-platform-reqs --prefer-dist

# Copier le reste des fichiers de l'application
COPY . .

# Copier les assets buildés depuis l'étape Node
COPY --from=node_builder /app/public/build ./public/build

# Générer l'autoloader optimisé et exécuter les scripts
RUN composer dump-autoload --optimize --no-dev

# Créer les répertoires nécessaires et configurer les permissions
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/www/html/public && \
    chown -R www-data:www-data /var/www/html/var /var/www/html/public && \
    chmod -R 775 /var/www/html/var

# Configuration Nginx
COPY <<EOF /etc/nginx/http.d/default.conf
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;

    location / {
        try_files \$uri /index.php\$is_args\$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
EOF

# Configuration Supervisor
COPY <<EOF /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

# Variables d'environnement par défaut
ENV APP_ENV=prod
ENV APP_DEBUG=0

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
