<?php

namespace App\Entity;

use App\Enum\StatutCommande;
use DateTimeImmutable;

/** Étape du suivi d'une commande : un statut et sa date de passage (table `commande_statut`). */
class EtapeSuivi
{
    public function __construct(
        private StatutCommande $statut,
        private DateTimeImmutable $date,
    ) {
    }

    public function getStatut(): StatutCommande
    {
        return $this->statut;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }
}
