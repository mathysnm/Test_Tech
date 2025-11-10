-- ============================================
-- MISE À JOUR DES DATES - DONNÉES DE TEST UNIQUEMENT
-- 40 tickets répartis sur 7 jours avec temps de résolution réalistes
-- ============================================

-- JOUR -7 (6 tickets) - Temps résolution: 2-19h
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '8 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '6 hours' WHERE title = 'Erreur critique lors du paiement';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '7 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '17 hours' WHERE title = 'Rapports mensuels inaccessibles';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '6 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '1 hour' WHERE title = 'Creation sous-comptes utilisateur';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '5 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '1 hour' WHERE title = 'Question politique confidentialite';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '7 days' - INTERVAL '2 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '7 hours' WHERE title = 'Affichage mobile Android';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '7 days', updated_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '18 hours' WHERE title = 'Formation nouvelles fonctionnalites';

-- JOUR -6 (5 tickets) - Temps résolution: 1-19h
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '8 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '5 hours' WHERE title = 'Erreur connexion portail';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '7 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '17 hours' WHERE title = 'Export PDF incomplet';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '6 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '3 hours' WHERE title = 'Modification coordonnees bancaires';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '4 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '9 hours' WHERE title = 'Notifications email non recues';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '6 days' - INTERVAL '2 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '6 days' WHERE title = 'Demande devis personnalise';

-- JOUR -5 (6 tickets) - Temps résolution: 2-24h
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '9 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '5 hours' WHERE title = 'Lenteur chargement documents';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '8 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '17 hours' WHERE title = 'Synchronisation calendrier';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '7 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '2 hours' WHERE title = 'Question facturation annuelle';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '6 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '2 hours' WHERE title = 'Authentification double facteur';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '5 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '20 hours' WHERE title = 'Recherche avancee vide';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '3 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '19 hours' WHERE title = 'Migration donnees';

-- JOUR -4 (5 tickets) - Temps résolution: 3-20h
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '9 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '1 hour' WHERE title = 'Modifier document partage';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '8 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '18 hours' WHERE title = 'Export Excel corrompu';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '7 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '4 hours' WHERE title = 'Ajout membre equipe';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '5 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '20 hours' WHERE title = 'Graphiques dashboard vides';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '3 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '4 days' WHERE title = 'Integration API';

-- JOUR -3 (6 tickets) - Temps résolution: 1-18h
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '10 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '6 hours' WHERE title = 'Perte donnees sauvegarde';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '9 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '21 hours' WHERE title = 'Fonction tri documents';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '8 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '3 hours' WHERE title = 'Reinitialisation mot de passe';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '7 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '4 hours' WHERE title = 'Upload fichiers volumineux';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '5 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '2 hours' WHERE title = 'Sauvegardes automatiques';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '4 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '16 hours' WHERE title = 'Rapport mensuel incomplet';

-- JOUR -2 (5 tickets IN_PROGRESS) - Créés il y a 2 jours, en cours
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '8 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '8 hours' WHERE title = 'Creation nouveau projet';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '7 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '7 hours' WHERE title = 'Limite stockage atteinte';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '5 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '5 hours' WHERE title = 'Acces dossier archive';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '4 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '4 hours' WHERE title = 'Partage lien externe';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '2 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '2 hours' WHERE title = 'Suppression compte';

-- JOUR -1 (4 tickets récents) - 2 IN_PROGRESS, 2 OPEN
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '6 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '6 hours' WHERE title = 'Telechargement pieces jointes';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '4 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '4 hours' WHERE title = 'Notifications push frequentes';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '3 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '3 hours' WHERE title = 'Mode hors ligne';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '2 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '2 hours' WHERE title = 'Formulaire contact';

-- AUJOURD'HUI (3 tickets OPEN très récents)
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '3 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '3 hours' WHERE title = 'Chargement tableau bord';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '2 hours', updated_at = CURRENT_TIMESTAMP - INTERVAL '2 hours' WHERE title = 'Consultation logs activite';
UPDATE ticket SET created_at = CURRENT_TIMESTAMP - INTERVAL '1 hour', updated_at = CURRENT_TIMESTAMP - INTERVAL '1 hour' WHERE title = 'Import fichier CSV';
