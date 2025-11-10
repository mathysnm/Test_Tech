<?php
namespace App\DataFixtures;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $em): void
    {
        // UTILISATEURS
        $marie = new User();
        $marie->setName("Marie Dubois")->setEmail("marie.dubois@client.com")->setRole("CLIENT");
        $em->persist($marie);

        $jean = new User();
        $jean->setName("Jean Martin")->setEmail("jean.martin@client.com")->setRole("CLIENT");
        $em->persist($jean);

        $claire = new User();
        $claire->setName("Claire Rousseau")->setEmail("claire.rousseau@client.com")->setRole("CLIENT");
        $em->persist($claire);

        $sophie = new User();
        $sophie->setName("Sophie Bernard")->setEmail("sophie.bernard@support.com")->setRole("AGENT");
        $em->persist($sophie);

        $pierre = new User();
        $pierre->setName("Pierre Dupont")->setEmail("pierre.dupont@support.com")->setRole("AGENT");
        $em->persist($pierre);

        $lucas = new User();
        $lucas->setName("Lucas Moreau")->setEmail("lucas.moreau@support.com")->setRole("AGENT");
        $em->persist($lucas);

        $thomas = new User();
        $thomas->setName("Thomas Petit")->setEmail("thomas.petit@manager.com")->setRole("MANAGER");
        $em->persist($thomas);

        $em->flush();

        // TICKETS - DONNEES DE TEST UNIQUEMENT
        $this->createTicket($em, "Erreur critique lors du paiement", "Code erreur PAYMENT_GATEWAY_TIMEOUT urgent", "HIGH", "RESOLVED", $marie, $sophie);
        $this->createTicket($em, "Rapports mensuels inaccessibles", "Besoin des rapports pour reunion direction", "HIGH", "RESOLVED", $jean, $pierre);
        $this->createTicket($em, "Creation sous-comptes utilisateur", "3 sous-comptes consultation seule", "MEDIUM", "RESOLVED", $claire, $lucas);
        $this->createTicket($em, "Question politique confidentialite", "Precisions traitement donnees RGPD", "LOW", "RESOLVED", $marie, $lucas);
        $this->createTicket($em, "Affichage mobile Android", "Boutons decales menu inaccessible", "MEDIUM", "RESOLVED", $jean, $sophie);
        $this->createTicket($em, "Formation nouvelles fonctionnalites", "Session formation equipe version 2.0", "LOW", "RESOLVED", $claire, $pierre);
        $this->createTicket($em, "Erreur connexion portail", "Erreur 500 persistante cache vide", "HIGH", "RESOLVED", $marie, $sophie);
        $this->createTicket($em, "Export PDF incomplet", "Factures PDF premiere page seulement", "MEDIUM", "RESOLVED", $marie, $pierre);
        $this->createTicket($em, "Modification coordonnees bancaires", "Mise a jour coordonnees prelevements", "LOW", "RESOLVED", $claire, $lucas);
        $this->createTicket($em, "Notifications email non recues", "Aucune notification 3 jours", "MEDIUM", "RESOLVED", $jean, $sophie);
        $this->createTicket($em, "Demande devis personnalise", "Abonnement entreprise 50 utilisateurs", "LOW", "RESOLVED", $claire, $lucas);
        $this->createTicket($em, "Lenteur chargement documents", "30 secondes impact productivite", "MEDIUM", "RESOLVED", $jean, $sophie);
        $this->createTicket($em, "Synchronisation calendrier", "Google Calendar ne synchronise plus", "HIGH", "RESOLVED", $marie, $pierre);
        $this->createTicket($em, "Question facturation annuelle", "Infos passage facturation annuelle", "LOW", "RESOLVED", $claire, $lucas);
        $this->createTicket($em, "Authentification double facteur", "Code SMS verification jamais recu", "HIGH", "RESOLVED", $jean, $sophie);
        $this->createTicket($em, "Recherche avancee vide", "Aucun resultat criteres larges", "MEDIUM", "RESOLVED", $marie, $pierre);
        $this->createTicket($em, "Migration donnees", "Processus migration ancien systeme", "MEDIUM", "RESOLVED", $claire, $lucas);
        $this->createTicket($em, "Modifier document partage", "Erreur Permission denied avec droits", "HIGH", "RESOLVED", $marie, $sophie);
        $this->createTicket($em, "Export Excel corrompu", "Fichiers Excel ne s'ouvrent pas", "MEDIUM", "RESOLVED", $jean, $pierre);
        $this->createTicket($em, "Ajout membre equipe", "Creation acces permissions appropriees", "LOW", "RESOLVED", $claire, $lucas);
        $this->createTicket($em, "Graphiques dashboard vides", "Statistiques vides avec donnees", "MEDIUM", "RESOLVED", $marie, $sophie);
        $this->createTicket($em, "Integration API", "Documentation technique API CRM", "LOW", "RESOLVED", $jean, $lucas);
        $this->createTicket($em, "Perte donnees sauvegarde", "Formulaire vide apres sauvegarder", "HIGH", "RESOLVED", $claire, $pierre);
        $this->createTicket($em, "Fonction tri documents", "Tri date ne fonctionne pas", "MEDIUM", "RESOLVED", $jean, $sophie);
        $this->createTicket($em, "Reinitialisation mot de passe", "Lien reinitialisation non recu", "MEDIUM", "RESOLVED", $marie, $lucas);
        $this->createTicket($em, "Upload fichiers volumineux", "Erreur timeout plus 10 Mo", "HIGH", "RESOLVED", $claire, $pierre);
        $this->createTicket($em, "Sauvegardes automatiques", "Frequence backups automatiques", "LOW", "RESOLVED", $jean, $lucas);
        $this->createTicket($em, "Rapport mensuel incomplet", "2 semaines au lieu de 4", "MEDIUM", "RESOLVED", $marie, $sophie);
        $this->createTicket($em, "Creation nouveau projet", "Erreur Validation failed", "HIGH", "IN_PROGRESS", $marie, $sophie);
        $this->createTicket($em, "Limite stockage atteinte", "Augmenter espace ou nettoyer", "MEDIUM", "IN_PROGRESS", $jean, $pierre);
        $this->createTicket($em, "Acces dossier archive", "Restauration temporaire dossier", "LOW", "IN_PROGRESS", $claire, $lucas);
        $this->createTicket($em, "Partage lien externe", "Liens erreur 404 destinataires", "HIGH", "IN_PROGRESS", $marie, $sophie);
        $this->createTicket($em, "Suppression compte", "Suppression definitive donnees RGPD", "MEDIUM", "IN_PROGRESS", $jean, $lucas);
        $this->createTicket($em, "Telechargement pieces jointes", "Interruption 50 pourcent", "HIGH", "IN_PROGRESS", $claire, $pierre);
        $this->createTicket($em, "Notifications push frequentes", "Toutes les 5 minutes perturbant", "MEDIUM", "IN_PROGRESS", $jean, $sophie);
        $this->createTicket($em, "Mode hors ligne", "Fonctionnement sans connexion", "LOW", "OPEN", $marie, $lucas);
        $this->createTicket($em, "Formulaire contact", "Bouton Envoyer rien ne se passe", "MEDIUM", "OPEN", $claire, $pierre);
        $this->createTicket($em, "Chargement tableau bord", "Page blanche Loading infini", "HIGH", "OPEN", $marie, $sophie);
        $this->createTicket($em, "Consultation logs activite", "Historique 6 mois audit", "LOW", "OPEN", $jean, $lucas);
        $this->createTicket($em, "Import fichier CSV", "Erreur Format non supporte", "MEDIUM", "OPEN", $claire, $pierre);

        $em->flush();
    }

    private function createTicket(ObjectManager $em, string $title, string $desc, string $priority, string $status, User $creator, User $assignee): Ticket
    {
        $ticket = new Ticket();
        $ticket->setTitle($title)->setDescription($desc)->setPriority($priority)->setStatus($status)->setCreator($creator)->setAssignee($assignee);
        $em->persist($ticket);
        return $ticket;
    }
}
