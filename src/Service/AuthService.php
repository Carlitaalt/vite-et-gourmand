<?php

namespace App\Service;

use App\Core\Database;
use App\Entity\Utilisateur;
use App\Enum\Role;
use App\Exception\MetierException;
use App\Repository\TokenReinitialisationRepository;
use App\Repository\UtilisateurRepository;

/**
 * Inscription, connexion et réinitialisation du mot de passe.
 */
class AuthService
{
    /** 10 caractères minimum dont une minuscule, une majuscule, un chiffre et un caractère spécial. */
    public const REGEX_MOT_DE_PASSE = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/';
    private const VALIDITE_LIEN_MINUTES = 60;

    public function __construct(
        private UtilisateurRepository $utilisateurs = new UtilisateurRepository(),
        private TokenReinitialisationRepository $jetons = new TokenReinitialisationRepository(),
        private NotificationService $notifications = new NotificationService(),
    ) {
    }

    public static function validerMotDePasse(string $motDePasse, string $confirmation): void
    {
        if (!preg_match(self::REGEX_MOT_DE_PASSE, $motDePasse)) {
            throw new MetierException('Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.');
        }
        if ($motDePasse !== $confirmation) {
            throw new MetierException('Les mots de passe ne correspondent pas.');
        }
    }

    /** Crée un compte avec le rôle "utilisateur" et envoie l'e-mail de bienvenue. */
    public function inscrire(array $donnees): Utilisateur
    {
        $prenom = trim($donnees['prenom'] ?? '');
        $nom = trim($donnees['nom'] ?? '');
        $email = trim($donnees['email'] ?? '');
        $telephone = trim($donnees['telephone'] ?? '');
        $adresse = trim($donnees['adresse'] ?? '');
        $ville = trim($donnees['ville'] ?? '');
        $pays = trim($donnees['pays'] ?? '') ?: 'France';

        if ($prenom === '' || $nom === '' || $email === '' || $telephone === '' || $adresse === '') {
            throw new MetierException('Veuillez remplir tous les champs obligatoires.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new MetierException('Adresse e-mail invalide.');
        }

        self::validerMotDePasse($donnees['mot_de_passe'] ?? '', $donnees['mot_de_passe_conf'] ?? '');

        if ($this->utilisateurs->emailExiste($email)) {
            throw new MetierException('Cette adresse e-mail est déjà utilisée.');
        }

        $utilisateur = new Utilisateur(
            null,
            Role::Utilisateur,
            $prenom,
            $nom,
            $email,
            password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT),
            $telephone,
            $adresse,
            $ville ?: null,
            $pays,
        );

        $this->utilisateurs->inserer($utilisateur);
        $this->notifications->bienvenue($utilisateur);

        return $utilisateur;
    }

    public function authentifier(string $email, string $motDePasse): Utilisateur
    {
        $utilisateur = $this->utilisateurs->findByEmail(trim($email));

        // Même message que l'e-mail existe ou non : on ne révèle pas quels comptes existent
        if ($utilisateur === null || !$utilisateur->verifierMotDePasse($motDePasse)) {
            throw new MetierException('Email ou mot de passe incorrect.');
        }
        if (!$utilisateur->estActif()) {
            throw new MetierException("Ce compte a été désactivé. Contactez l'administrateur.");
        }

        return $utilisateur;
    }

    /** Envoie un lien de réinitialisation. Ne signale jamais si l'adresse existe (anti-énumération). */
    public function demanderReinitialisation(string $email): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new MetierException('Adresse e-mail invalide.');
        }

        $utilisateur = $this->utilisateurs->findByEmail($email);
        if ($utilisateur === null || !$utilisateur->estActif()) {
            return;
        }

        $jeton = bin2hex(random_bytes(32));
        $this->jetons->creer($utilisateur->getId(), $jeton, self::VALIDITE_LIEN_MINUTES);
        $this->notifications->lienReinitialisation($utilisateur, $jeton);
    }

    public function jetonEstValide(string $jeton): bool
    {
        return $jeton !== '' && $this->jetons->trouverUtilisateurValide($jeton) !== null;
    }

    public function reinitialiserMotDePasse(string $jeton, string $motDePasse, string $confirmation): void
    {
        $utilisateurId = $this->jetons->trouverUtilisateurValide($jeton);
        $utilisateur = $utilisateurId !== null ? $this->utilisateurs->findById($utilisateurId) : null;

        if ($utilisateur === null) {
            throw new MetierException('Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.');
        }

        self::validerMotDePasse($motDePasse, $confirmation);
        $utilisateur->changerMotDePasse(password_hash($motDePasse, PASSWORD_DEFAULT));

        Database::transaction(function () use ($utilisateur, $jeton): void {
            $this->utilisateurs->mettreAJourMotDePasse($utilisateur);
            $this->jetons->marquerUtilise($jeton);
        });
    }
}
