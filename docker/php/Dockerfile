# ============================================
# Dockerfile PHP 8.3-FPM pour Symfony 7
# ============================================
# 
# Image de base: PHP 8.3-FPM officielle (FPM = FastCGI Process Manager)
# Extensions installées:
# - pdo_pgsql: Connexion PostgreSQL pour Doctrine
# - zip: Requis par Composer pour l'installation des packages
#
# Outils: Composer 2 (gestionnaire de dépendances PHP)
# ============================================

FROM php:8.3-fpm

# ==========================================
# Installation des dépendances système et extensions PHP
# ==========================================
# Dépendances système:
# - git: Requis par Composer pour cloner les dépendances
# - unzip: Extraction rapide des archives Composer
# - libpq-dev: Bibliothèques PostgreSQL
# - libzip-dev: Bibliothèques ZIP
# - zip: Utilitaire de compression
#
# Extensions PHP:
# - pdo: PHP Data Objects (interface d'accès BDD)
# - pdo_pgsql: Pilote PostgreSQL pour Doctrine ORM
# - zip: Manipulation d'archives ZIP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ==========================================
# Installation de Composer 2
# ==========================================
# COPY depuis l'image officielle Composer (multi-stage pattern)
# Version 2 spécifique pour compatibilité Symfony 7
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ==========================================
# Configuration du répertoire de travail
# ==========================================
# Répertoire monté depuis docker-compose.yml (./backend:/var/www/html)
WORKDIR /var/www/html

# ==========================================
# Copie des fichiers d'application
# ==========================================
# Note: En développement, le volume monté écrase cette copie
# Cette ligne est utile pour les builds de production
COPY ./backend /var/www/html

# Copie du script d'entrypoint
COPY ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
