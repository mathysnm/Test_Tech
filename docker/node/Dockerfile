# ============================================
# Dockerfile Node 20 Alpine pour Vue.js + Vite
# ============================================
# 
# Image de base: Node.js 20 LTS sur Alpine Linux
# Taille: ~150MB (vs ~900MB pour node:20 standard)
# 
# Outils: npm (inclus), git (pour dépendances GitHub)
# Usage: Dev server Vite avec Hot Module Replacement
# ============================================

FROM node:20-alpine

# ==========================================
# Installation des outils système
# ==========================================
# git: Requis par npm pour installer certaines dépendances depuis GitHub
RUN apk add --no-cache git

# ==========================================
# Configuration du répertoire de travail
# ==========================================
# Répertoire monté depuis docker-compose.yml (./frontend:/app)
WORKDIR /app

# ==========================================
# Copie des fichiers du frontend
# ==========================================
# Note: En développement, le volume monté écrase cette copie
# Cette ligne est utile pour les builds de production
COPY ../../frontend /app

# ==========================================
# Exposition du port Vite
# ==========================================
# Port 5173: Dev server Vite avec HMR (Hot Module Replacement)
EXPOSE 5173

# ==========================================
# Commande par défaut
# ==========================================
# Override par docker-compose.yml: npm install && npm run dev
# --host 0.0.0.0: Permet l'accès depuis l'hôte Windows
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
