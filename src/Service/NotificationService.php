<?php

namespace App\Service;

use App\Core\Mailer;
use App\Entity\Commande;
use App\Entity\Utilisateur;

/**
 * E-mails automatiques prévus par le cahier des charges.
 * Toutes les données saisies par les utilisateurs sont échappées avant d'être insérées dans le HTML.
 */
class NotificationService
{
    public function __construct(private Mailer $mailer = new Mailer())
    {
    }

    public function bienvenue(Utilisateur $utilisateur): void
    {
        $this->envoyer($utilisateur->getEmail(), 'Bienvenue chez Vite & Gourmand !', "
            <p>Bonjour {$this->e($utilisateur->getPrenom())},</p>
            <p>Votre compte a bien été créé. Vous pouvez dès maintenant commander nos menus pour vos événements.</p>
            <p><a href=\"{$this->url('pages/connexion.php')}\">Me connecter</a></p>
        ");
    }

    public function confirmationCommande(Commande $commande): void
    {
        $client = $commande->getClient();
        if ($client === null) {
            return;
        }

        $livraison = $commande->getPrixLivraison() > 0 ? $this->prix($commande->getPrixLivraison()) : 'offerte';

        $this->envoyer($client->getEmail(), "Confirmation de votre commande n°{$commande->getId()}", "
            <p>Bonjour {$this->e($client->getPrenom())},</p>
            <p>Nous avons bien reçu votre commande :</p>
            <ul>
                <li>Menu : {$this->e($commande->getMenuTitre())}</li>
                <li>Nombre de personnes : {$commande->getNombrePersonnes()}</li>
                <li>Prestation : le {$commande->getDatePrestation()->format('d/m/Y')} à {$commande->getHeureLivraison()}</li>
                <li>Adresse : {$this->e($commande->getAdresseLivraison())}, {$this->e($commande->getVilleLivraison())}</li>
                <li>Livraison : {$livraison}</li>
                <li><strong>Total : {$this->prix($commande->getPrixTotal())}</strong></li>
            </ul>
            <p>Vous pouvez suivre, modifier ou annuler votre commande depuis votre espace tant qu'elle n'a pas été acceptée par notre équipe.</p>
        ");
    }

    public function lienReinitialisation(Utilisateur $utilisateur, string $jeton): void
    {
        $lien = $this->url('pages/reintialiser.php?token=' . urlencode($jeton));

        $this->envoyer($utilisateur->getEmail(), 'Réinitialisation de votre mot de passe', "
            <p>Bonjour {$this->e($utilisateur->getPrenom())},</p>
            <p>Pour choisir un nouveau mot de passe, cliquez sur le lien suivant (valable 1 heure) :</p>
            <p><a href=\"{$this->e($lien)}\">{$this->e($lien)}</a></p>
            <p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.</p>
        ");
    }

    /** Le mot de passe n'est volontairement pas communiqué : l'employé doit le demander à l'administrateur. */
    public function compteEmployeCree(Utilisateur $employe): void
    {
        $this->envoyer($employe->getEmail(), 'Votre compte employé Vite & Gourmand', "
            <p>Bonjour {$this->e($employe->getPrenom())},</p>
            <p>Un compte employé a été créé pour vous. Votre identifiant est cette adresse e-mail.</p>
            <p>Pour des raisons de sécurité, votre mot de passe ne vous est pas envoyé : rapprochez-vous de l'administrateur pour l'obtenir.</p>
        ");
    }

    public function messageContact(string $titre, string $description, string $emailVisiteur): bool
    {
        return $this->envoyer(getenv('MAIL_CONTACT') ?: 'contact@vite-gourmand.fr', '[Contact] ' . $titre, "
            <p>Nouveau message de {$this->e($emailVisiteur)} :</p>
            <p><strong>{$this->e($titre)}</strong></p>
            <p>" . nl2br($this->e($description)) . "</p>
        ", $emailVisiteur);
    }

    public function retourMateriel(Commande $commande): void
    {
        $this->envoyerAuClient($commande, "Commande n°{$commande->getId()} : restitution du matériel prêté", "
            <p>Votre commande a été livrée avec du matériel prêté par notre équipe.</p>
            <p>Merci de prendre contact avec nous pour organiser sa restitution.
            <strong>Si le matériel n'est pas restitué sous 10 jours ouvrés, des frais de 600 € vous seront facturés</strong>,
            conformément à nos <a href=\"{$this->url('pages/cgv.php')}\">conditions générales de vente</a>.</p>
        ");
    }

    public function commandeTerminee(Commande $commande): void
    {
        $this->envoyerAuClient($commande, "Commande n°{$commande->getId()} terminée : donnez-nous votre avis", "
            <p>Votre commande « {$this->e($commande->getMenuTitre())} » est terminée. Merci de votre confiance !</p>
            <p>Connectez-vous à votre espace pour nous laisser une note et un commentaire :
            <a href=\"{$this->url('pages/mon-compte.php')}\">donner mon avis</a>.</p>
        ");
    }

    public function commandeAnnulee(Commande $commande): void
    {
        $this->envoyerAuClient($commande, "Commande n°{$commande->getId()} annulée", "
            <p>Suite à notre échange, votre commande « {$this->e($commande->getMenuTitre())} » a été annulée.</p>
            <p>Motif : {$this->e($commande->getMotifAnnulation() ?? '')}</p>
        ");
    }

    private function envoyerAuClient(Commande $commande, string $sujet, string $contenu): void
    {
        $client = $commande->getClient();
        if ($client !== null) {
            $this->envoyer($client->getEmail(), $sujet, "<p>Bonjour {$this->e($client->getPrenom())},</p>" . $contenu);
        }
    }

    private function envoyer(string $destinataire, string $sujet, string $contenu, ?string $repondreA = null): bool
    {
        $html = '<div style="font-family:sans-serif;color:#2c3e2d;max-width:600px;margin:auto;">'
            . '<h2 style="color:#2c4a2e;">Vite &amp; Gourmand</h2>'
            . $contenu
            . '<p style="color:#888;font-size:12px;">Vite &amp; Gourmand — Traiteur événementiel à Bordeaux</p>'
            . '</div>';

        return $this->mailer->envoyer($destinataire, $sujet, $html, $repondreA);
    }

    private function url(string $chemin): string
    {
        return rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/') . '/' . $chemin;
    }

    private function prix(float $montant): string
    {
        return number_format($montant, 2, ',', ' ') . ' €';
    }

    private function e(string $valeur): string
    {
        return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
    }
}
