<?php

namespace App\Enum;

/**
 * Rôles de l'application. Les valeurs correspondent aux role_id insérés par database/seed.sql.
 */
enum Role: int
{
    case Utilisateur = 1;
    case Employe = 2;
    case Administrateur = 3;

    public function libelle(): string
    {
        return match ($this) {
            self::Utilisateur => 'Utilisateur',
            self::Employe => 'Employé',
            self::Administrateur => 'Administrateur',
        };
    }

    /** Hiérarchie : un rôle possède les droits de tous les rôles inférieurs. */
    public function aAuMoins(Role $roleRequis): bool
    {
        return $this->value >= $roleRequis->value;
    }
}
