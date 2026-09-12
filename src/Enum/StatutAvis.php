<?php

namespace App\Enum;

/**
 * Modération des avis. Les valeurs correspondent aux statut_avis_id de seed.sql.
 */
enum StatutAvis: int
{
    case EnAttente = 1;
    case Publie = 2;
    case Refuse = 3;

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Publie => 'Publié',
            self::Refuse => 'Refusé',
        };
    }

    public function classeCss(): string
    {
        return match ($this) {
            self::EnAttente => 'statut--attente',
            self::Publie => 'statut--termine',
            self::Refuse => 'statut--annule',
        };
    }
}
