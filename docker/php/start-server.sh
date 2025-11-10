#!/bin/sh
# Script de démarrage pour le serveur PHP intégré

cd /var/www/html/public

# Démarrer le serveur PHP intégré sur le port 8000
# Avec CORS headers configurés
php -S 0.0.0.0:8000 -t . /var/www/html/public/index.php
