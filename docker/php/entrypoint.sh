#!/bin/bash
set -e

echo "🚀 Initialisation de l'application..."

# Installer les dépendances Composer si nécessaire
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install --no-interaction --optimize-autoloader
else
    echo "✅ Dépendances Composer déjà installées"
fi

# Attendre que PostgreSQL soit prêt
echo "⏳ Attente de la base de données..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    sleep 1
done
echo "✅ Base de données prête"

# Créer la base de données si elle n'existe pas
echo "📦 Création de la base de données..."
php bin/console doctrine:database:create --if-not-exists --no-interaction || true

# Exécuter les migrations
echo "🔄 Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Charger les fixtures UNIQUEMENT si elles n'existent pas déjà
if ! php bin/console doctrine:query:sql "SELECT COUNT(*) FROM \"user\"" 2>/dev/null | grep -q "[1-9]"; then
    echo "📋 Chargement initial des fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction
    
    echo "📅 Application des dates réalistes sur les tickets..."
    # Exécuter le script SQL via Doctrine
    if [ -f "fixtures_dates.sql" ]; then
        while IFS= read -r line; do
            if [[ ! "$line" =~ ^[[:space:]]*$ ]] && [[ ! "$line" =~ ^-- ]]; then
                php bin/console doctrine:query:sql "$line" > /dev/null 2>&1 || true
            fi
        done < fixtures_dates.sql
        echo "✅ Dates appliquées avec succès"
    else
        echo "⚠️  Fichier fixtures_dates.sql non trouvé, dates non modifiées"
    fi
else
    echo "✅ Fixtures déjà présentes, skip"
fi

# Clear cache pour s'assurer que les routes sont bien chargées
echo "🧹 Nettoyage du cache Symfony..."
php bin/console cache:clear --no-interaction

echo "✅ Initialisation terminée !"

# Créer un fichier marker pour le healthcheck
touch /tmp/.init-done

# Exécuter la commande passée en argument (sera 'php -S ...' depuis docker-compose)
echo "🚀 Démarrage du serveur..."
exec "$@"
