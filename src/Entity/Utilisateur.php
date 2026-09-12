<?php

namespace App\Entity;

use App\Enum\Role;
use DateTimeImmutable;

/**
 * Compte de l'application : client, employé ou administrateur (table `utilisateur`).
 */
class Utilisateur
{
    public function __construct(
        private ?int $id,
        private Role $role,
        private string $prenom,
        private string $nom,
        private string $email,
        private string $motDePasseHash,
        private ?string $telephone = null,
        private ?string $adressePostale = null,
        private ?string $ville = null,
        private ?string $pays = null,
        private bool $actif = true,
        private ?DateTimeImmutable $creeLe = null,
        private ?DateTimeImmutable $modifieLe = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getInitiales(): string
    {
        return mb_strtoupper(mb_substr($this->prenom, 0, 1) . mb_substr($this->nom, 0, 1));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMotDePasseHash(): string
    {
        return $this->motDePasseHash;
    }

    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasseHash);
    }

    public function changerMotDePasse(string $nouveauHash): void
    {
        $this->motDePasseHash = $nouveauHash;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function getAdressePostale(): ?string
    {
        return $this->adressePostale;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function estActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): void
    {
        $this->actif = $actif;
    }

    public function getCreeLe(): ?DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function getModifieLe(): ?DateTimeImmutable
    {
        return $this->modifieLe;
    }

    public function modifierProfil(
        string $prenom,
        string $nom,
        string $email,
        ?string $telephone,
        ?string $adressePostale,
        ?string $ville,
        ?string $pays
    ): void {
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->adressePostale = $adressePostale;
        $this->ville = $ville;
        $this->pays = $pays;
    }
}
