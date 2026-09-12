<?php

namespace App\Enum;

/**
 * Cycle de vie d'une commande (cahier des charges). Les valeurs correspondent aux statut_id de seed.sql.
 */
enum StatutCommande: int
{
    case EnAttente = 1;
    case Acceptee = 2;
    case EnPreparation = 3;
    case EnLivraison = 4;
    case Livree = 5;
    case RetourMateriel = 6;
    case Terminee = 7;
    case Annulee = 8;

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Acceptee => 'Acceptée',
            self::EnPreparation => 'En préparation',
            self::EnLivraison => 'En cours de livraison',
            self::Livree => 'Livrée',
            self::RetourMateriel => 'En attente du retour de matériel',
            self::Terminee => 'Terminée',
            self::Annulee => 'Annulée',
        };
    }

    public function classeCss(): string
    {
        return match ($this) {
            self::EnAttente => 'statut--attente',
            self::Acceptee => 'statut--accepte',
            self::EnPreparation => 'statut--prep',
            self::EnLivraison => 'statut--livraison',
            self::Livree => 'statut--livre',
            self::RetourMateriel => 'statut--materiel',
            self::Terminee => 'statut--termine',
            self::Annulee => 'statut--annule',
        };
    }

    /**
     * Statuts suivants autorisés (hors annulation, qui a sa propre procédure).
     * Le choix entre "retour matériel" et "terminée" après livraison dépend du prêt de matériel : voir Commande.
     *
     * @return StatutCommande[]
     */
    public function suivantsPossibles(): array
    {
        return match ($this) {
            self::EnAttente => [self::Acceptee],
            self::Acceptee => [self::EnPreparation],
            self::EnPreparation => [self::EnLivraison],
            self::EnLivraison => [self::Livree],
            self::Livree => [self::RetourMateriel, self::Terminee],
            self::RetourMateriel => [self::Terminee],
            self::Terminee, self::Annulee => [],
        };
    }

    public function estFinal(): bool
    {
        return $this === self::Terminee || $this === self::Annulee;
    }

    public function estEnCours(): bool
    {
        return !$this->estFinal() && $this !== self::EnAttente;
    }
}
