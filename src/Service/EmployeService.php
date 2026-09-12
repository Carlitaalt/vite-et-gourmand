<?php

namespace App\Service;

use App\Core\Database;
use App\Entity\Employe;
use App\Entity\Utilisateur;
use App\Enum\Role;
use App\Exception\MetierException;
use App\Repository\EmployeRepository;
use App\Repository\UtilisateurRepository;

/**
 * Gestion des comptes employés par l'administrateur.
 * Il n'est volontairement pas possible de créer un compte administrateur depuis l'application.
 */
class EmployeService
{
    public function __construct(
        private UtilisateurRepository $utilisateurs = new UtilisateurRepository(),
        private EmployeRepository $employes = new EmployeRepository(),
        private NotificationService $notifications = new NotificationService(),
    ) {
    }

    /** @return Employe[] */
    public function tousLesEmployes(): array
    {
        return $this->employes->findAll();
    }

    public function trouver(int $utilisateurId): ?Employe
    {
        return $this->employes->findByUtilisateurId($utilisateurId);
    }

    public function creer(array $donnees): Employe
    {
        $prenom = trim($donnees['prenom'] ?? '');
        $nom = trim($donnees['nom'] ?? '');
        $email = trim($donnees['email'] ?? '');

        if ($prenom === '' || $nom === '' || $email === '') {
            throw new MetierException('Le prénom, le nom et l\'adresse e-mail sont obligatoires.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new MetierException('Adresse e-mail invalide.');
        }
        if ($this->utilisateurs->emailExiste($email)) {
            throw new MetierException('Cette adresse e-mail est déjà utilisée.');
        }

        AuthService::validerMotDePasse($donnees['password'] ?? '', $donnees['password_confirm'] ?? '');

        $utilisateur = new Utilisateur(
            null,
            Role::Employe,
            $prenom,
            $nom,
            $email,
            password_hash($donnees['password'], PASSWORD_DEFAULT),
            trim($donnees['telephone'] ?? '') ?: null,
            trim($donnees['adresse'] ?? '') ?: null,
            trim($donnees['ville'] ?? '') ?: null,
            'France',
        );
        $employe = new Employe($utilisateur, trim($donnees['poste'] ?? '') ?: 'Employé', $this->salaire($donnees['salaire'] ?? null));

        Database::transaction(function () use ($utilisateur, $employe): void {
            $this->utilisateurs->inserer($utilisateur);
            $this->employes->inserer($employe);
        });

        $this->notifications->compteEmployeCree($utilisateur);

        return $employe;
    }

    public function modifierContrat(int $utilisateurId, string $poste, mixed $salaire): void
    {
        $employe = $this->employeExistant($utilisateurId);
        $employe->modifierContrat(trim($poste) ?: 'Employé', $this->salaire($salaire));
        $this->employes->mettreAJourContrat($employe);
    }

    /** Rendre un compte inutilisable (départ de l'entreprise) ou le réactiver. */
    public function changerActivation(int $utilisateurId, bool $actif): void
    {
        $utilisateur = $this->employeExistant($utilisateurId)->getUtilisateur();
        $utilisateur->setActif($actif);
        $this->utilisateurs->mettreAJourActivation($utilisateur);
    }

    /** Vérifie qu'il s'agit bien d'un employé : impossible de désactiver un client ou l'administrateur par ce biais. */
    private function employeExistant(int $utilisateurId): Employe
    {
        $employe = $this->employes->findByUtilisateurId($utilisateurId);

        if ($employe === null || $employe->getUtilisateur()->getRole() !== Role::Employe) {
            throw new MetierException('Employé introuvable.');
        }

        return $employe;
    }

    private function salaire(mixed $valeur): ?float
    {
        return is_numeric($valeur) && (float) $valeur >= 0 ? (float) $valeur : null;
    }
}
