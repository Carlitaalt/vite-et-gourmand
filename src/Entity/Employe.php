<?php

namespace App\Entity;

use DateTimeImmutable;

/**
 * Fiche employé (table `employes`), liée à un compte Utilisateur (composition).
 */
class Employe
{
    public function __construct(
        private Utilisateur $utilisateur,
        private string $poste,
        private ?float $salaireHoraire = null,
        private ?DateTimeImmutable $dateEmbauche = null,
    ) {
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getPoste(): string
    {
        return $this->poste;
    }

    public function getSalaireHoraire(): ?float
    {
        return $this->salaireHoraire;
    }

    public function getDateEmbauche(): ?DateTimeImmutable
    {
        return $this->dateEmbauche;
    }

    public function modifierContrat(string $poste, ?float $salaireHoraire): void
    {
        $this->poste = $poste;
        $this->salaireHoraire = $salaireHoraire;
    }
}
