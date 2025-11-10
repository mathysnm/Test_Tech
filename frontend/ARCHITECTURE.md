# Architecture Frontend - Vue.js 3 + Vite

## 📁 Structure du projet

```
frontend/src/
├── assets/              # Ressources statiques
│   └── styles/         # CSS organisé
│       └── base.css   # Variables CSS + Reset + Thème
├── components/          # Composants réutilisables
│   ├── common/         # Composants génériques (TODO)
│   ├── layout/         # Layout components (TODO)
│   └── tickets/        # Composants spécifiques tickets
│       ├── TicketList.vue
│       └── StatsCard.vue
├── composables/         # Logique réutilisable Vue 3 (TODO)
├── views/               # Pages/Vues
│   ├── LoginView.vue
│   └── TicketsView.vue
├── stores/              # Pinia stores
│   ├── auth.js        # Authentification
│   └── tickets.js     # Gestion tickets (refactorisé)
├── router/              # Configuration routing
│   └── index.js
├── services/            # Couche API (nouveau)
│   ├── api.js          # Configuration Axios centralisée
│   └── ticketService.js # Appels API tickets
├── utils/               # Fonctions utilitaires (nouveau)
│   └── formatters.js   # formatDate, formatStatus, etc.
├── constants/           # Constantes de l'app (nouveau)
│   └── index.js        # STATUS, PRIORITY, ROLES, couleurs
├── App.vue
└── main.js
```

## 🏗️ Principes d'architecture

### 1. **Séparation des responsabilités**

- **Views** : Pages complètes, assemblage de composants
- **Components** : Blocs réutilisables, pas de logique métier
- **Stores** : État global, logique métier simple
- **Services** : Appels API, communication backend
- **Utils** : Fonctions pures, transformations de données
- **Constants** : Valeurs fixes, configuration

### 2. **Flux de données**

```
User Interaction
    ↓
Vue Component (template + script)
    ↓
Pinia Store (state management)
    ↓
Service Layer (API calls)
    ↓
Backend API
```

### 3. **Organisation des fichiers**

#### Composants (`components/`)
- **common/** : Boutons, cards, inputs génériques
- **layout/** : Header, sidebar, footer
- **tickets/** : Composants spécifiques au domaine tickets

#### Services (`services/`)
- Un fichier par domaine fonctionnel
- Export de fonctions async
- Gestion des erreurs déléguée aux stores

#### Utils (`utils/`)
- Fonctions pures, sans état
- Formatage, validation, calculs

### 4. **Conventions de nommage**

- **Composants** : PascalCase (ex: `TicketList.vue`)
- **Stores** : camelCase (ex: `useTicketStore`)
- **Services** : camelCase + suffix "Service" (ex: `ticketService.js`)
- **Utils** : camelCase (ex: `formatters.js`)

## 🎨 Styles

### Variables CSS
Toutes les variables de thème sont dans `assets/styles/base.css` :
- Couleurs : `--color-primary`, `--color-accent`, etc.
- Espacements : `--spacing-xs` à `--spacing-2xl`
- Typographie : `--font-size-xs` à `--font-size-3xl`
- Ombres : `--shadow-sm` à `--shadow-xl`

### Approche
- **Scoped styles** dans les composants
- **Variables CSS** pour la cohérence
- **BEM** optionnel pour les classes complexes

## 📦 Dépendances principales

- **Vue 3** : Framework progressif
- **Vue Router** : Navigation SPA
- **Pinia** : State management
- **Axios** : HTTP client
- **Vite** : Build tool

## 🔄 Prochaines étapes

- [ ] Créer composants `common/` (Button, Card, Input)
- [ ] Créer composants `layout/` (Header, Sidebar)
- [ ] Ajouter composables pour logique réutilisable
- [ ] Compléter les tests unitaires
- [ ] Améliorer la gestion d'erreurs globale
- [ ] Ajouter un loader global

## 📝 Notes

- **Pas de styles globaux** dans les composants
- **Import des services** via `@/services/`
- **Import des constantes** via `@/constants`
- **Validation des props** dans tous les composants
