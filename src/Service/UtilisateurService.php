<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Exception\MetierException;
use App\Repository\CommandeRepository;
use App\Repository\UtilisateurRepository;

/**
 * Gestion de son compte par le client : profil et suppression (droit à l'effacement RGPD).
 */
class UtilisateurService
{
    public function __construct(
        private UtilisateurRepository $utilisateurs = new UtilisateurRepository(),
        private CommandeRepository $commandes = new CommandeRepository(),
    ) {
    }

    public function trouver(int $id): Utilisateur
    {
        return $this->utilisateurs->findById($id) ?? throw new MetierException('Compte introuvable.');
    }

    public function modifierProfil(int $id, array $donnees): Utilisateur
    {
        $utilisateur = $this->trouver($id);

        $prenom = trim($donnees['prenom'] ?? '');
        $nom = trim($donnees['nom'] ?? '');
        $email = trim($donnees['email'] ?? '');

        if ($prenom === '' || $nom === '' || $email === '') {
            throw new MetierException('Le prénom, le nom et l\'adresse e-mail sont obligatoires.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new MetierException('Adresse e-mail invalide.');
        }
        if ($this->utilisateurs->emailExiste($email, $id)) {
            throw new MetierException('Cette adresse e-mail est déjà utilisée par un autre compte.');
        }

        $utilisateur->modifierProfil(
            $prenom,
            $nom,
            $email,
            trim($donnees['telephone'] ?? '') ?: null,
            trim($donnees['adresse'] ?? '') ?: null,
            trim($donnees['ville'] ?? '') ?: null,
            trim($donnees['pays'] ?? '') ?: null,
        );
        $this->utilisateurs->mettreAJourProfil($utilisateur);

        return $utilisateur;
    }

    /**
     * Supprime le compte. S'il a déjà passé des commandes, elles doivent être conservées (comptabilité) :
     * le compte est alors anonymisé et désactivé plutôt que supprimé.
     */
    public function supprimerCompte(int $id): void
    {
        if (!$this->commandes->aDesCommandes($id)) {
            $this->utilisateurs->supprimer($id);
            return;
        }

        $utilisateur = $this->trouver($id);
        $utilisateur->modifierProfil('Compte', 'supprimé', "compte-supprime-$id@anonyme.invalid", null, null, null, null);
        $utilisateur->changerMotDePasse(password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT));
        $utilisateur->setActif(false);

        $this->utilisateurs->mettreAJourProfil($utilisateur);
        $this->utilisateurs->mettreAJourMotDePasse($utilisateur);
        $this->utilisateurs->mettreAJourActivation($utilisateur);
    }
}
