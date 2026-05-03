#!/bin/bash

# Script de déploiement pour MBIO VTC
# Usage: ./deploy.sh [production|local]

# Vérifier l'environnement
ENV=${1:-production}

echo "🚀 Déploiement MBIO VTC - $ENV"

# Créer le fichier .env selon l'environnement
if [ ! -f ".env" ]; then
    echo "📋 Création du fichier .env pour $ENV..."
    
    case $ENV in
        "production")
            cp env-production-example .env
            echo "✅ Fichier .env de production créé"
            ;;
        "local")
            cp .env.example .env
            php artisan key:generate
            echo "✅ Fichier .env local créé"
            ;;
        *)
            echo "❌ Environnement non reconnu: $ENV"
            echo "Usage: ./deploy.sh [production|local]"
            exit 1
            ;;
    esac
else
    echo "⚠️  Fichier .env existe déjà"
    read -p "Voulez-vous le remplacer pour $ENV ? (y/n): " replace
    if [ "$replace" = "y" ]; then
        case $ENV in
            "production")
                cp env-production-example .env
                echo "✅ Fichier .env de production remplacé"
                ;;
            "local")
                cp .env.example .env
                php artisan key:generate
                echo "✅ Fichier .env local remplacé"
                ;;
        esac
    fi
fi

echo ""
echo "📦 Installation des dépendances..."

case $ENV in
    "local")
        composer install
        ;;
    *)
        composer install --optimize-autoloader --no-dev --no-interaction
        ;;
esac

echo ""
echo "🔨 Build des assets..."

case $ENV in
    "local")
        npm install && npm run dev
        ;;
    *)
        npm install && npm run build
        ;;
esac

echo ""
echo "🗄️  Exécution des migrations..."

case $ENV in
    "local")
        php artisan migrate
        ;;
    *)
        php artisan migrate --force --no-interaction
        ;;
esac

echo ""
echo "⚡ Optimisation..."

if [ "$ENV" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize --no-interaction
fi

echo ""
echo "🔐 Permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "⚠️  Pas www-data (pas grave)"

echo ""
echo "🧹 Nettoyage du cache..."
php artisan cache:clear

echo ""
echo "✅ Déploiement $ENV terminé !"
echo ""

case $ENV in
    "production")
        echo "🌐 URL: https://mbio.messanga.gsi2026.com"
        echo "📱 Test API: curl -k https://mbio.messanga.gsi2026.com/api/"
        echo ""
        echo "🚀 Pour lancer le serveur :"
        echo "   php artisan serve --host=0.0.0.0 --port=8000"
        echo ""
        echo "🔄 Pour les queues :"
        echo "   php artisan queue:work --daemon"
        ;;
        "local")
        echo "🌐 URL: http://localhost:8000"
        echo "📱 Test API: curl http://localhost:8000/api/"
        echo ""
        echo "🚀 Pour lancer le serveur local :"
        echo "   php artisan serve"
        echo ""
        echo "🔄 Pour les queues :"
        echo "   php artisan queue:work"
        ;;
esac
