# Tests Backend

## Configuration requise

Avant d'exécuter les tests, il faut charger les fixtures dans la base de test :

```bash
docker exec -it tickets_app php bin/console doctrine:fixtures:load --env=test --no-interaction
```

## Exécution des tests

### Tous les tests
```bash
docker exec -it tickets_app php bin/phpunit
```

### Tests avec affichage détaillé
```bash
docker exec -it tickets_app php bin/phpunit --testdox
```

### Test spécifique
```bash
docker exec -it tickets_app php bin/phpunit tests/Controller/TicketControllerTest.php
```

### Avec mesure de couverture
```bash
docker exec -it tickets_app php bin/phpunit --coverage-text
```

## Tests de performance

Les tests incluent des mesures de temps de réponse :
- **Liste des tickets** : doit répondre en < 500ms
- **Statistiques** : doit répondre en < 300ms

Les temps sont affichés dans la sortie des tests avec le format :
```
✓ List tickets (MANAGER): 45.23ms - 8 tickets
✓ Stats (MANAGER): 127.45ms - Total: 8 tickets
```

## Utilisateurs de test (fixtures)

- **Alice Manager** (ID: 1) - Role: MANAGER
- **Bob Agent** (ID: 2) - Role: AGENT  
- **Charlie Agent** (ID: 3) - Role: AGENT
- **David Client** (ID: 4) - Role: CLIENT
- **Eve Client** (ID: 5) - Role: CLIENT
- **Frank Client** (ID: 6) - Role: CLIENT

## Tests d'authentification

Les endpoints `/api/tickets` et `/api/tickets/stats` requièrent le paramètre `user_id` :
- Sans `user_id` → 401 Unauthorized
- Avec `user_id` invalide → 404 User not found
- Avec `user_id` valide → Données filtrées selon le rôle

## Filtrage par rôle

- **CLIENT** : voit uniquement ses tickets (où `creator_id` = `user_id`)
- **AGENT** : voit uniquement les tickets assignés (où `assignee_id` = `user_id`)
- **MANAGER** : voit tous les tickets
