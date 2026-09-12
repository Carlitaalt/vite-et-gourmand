<?php

namespace App\Repository;

use App\Entity\Horaire;

class HoraireRepository extends AbstractRepository
{
    /** Horaires du lundi au dimanche. @return Horaire[] */
    public function findAll(): array
    {
        $lignes = $this->pdo->query('SELECT * FROM horaire ORDER BY horaire_id')->fetchAll();

        return array_map(
            fn(array $l) => new Horaire((int) $l['horaire_id'], $l['jour'], $l['heure_ouverture'], $l['heure_fermeture']),
            $lignes
        );
    }

    public function mettreAJour(int $horaireId, string $ouverture, string $fermeture): void
    {
        $this->pdo->prepare('UPDATE horaire SET heure_ouverture = ?, heure_fermeture = ? WHERE horaire_id = ?')
            ->execute([$ouverture, $fermeture, $horaireId]);
    }
}
