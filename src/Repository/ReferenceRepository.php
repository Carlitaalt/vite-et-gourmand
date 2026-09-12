<?php

namespace App\Repository;

use App\Entity\Allergene;
use App\Entity\Regime;
use App\Entity\Theme;

/**
 * Données de référence : thèmes, régimes et allergènes.
 */
class ReferenceRepository extends AbstractRepository
{
    /** @return Theme[] */
    public function findThemes(): array
    {
        $lignes = $this->pdo->query('SELECT theme_id, libelle FROM theme ORDER BY libelle')->fetchAll();

        return array_map(fn(array $l) => new Theme((int) $l['theme_id'], $l['libelle']), $lignes);
    }

    public function findThemeById(int $id): ?Theme
    {
        $stmt = $this->pdo->prepare('SELECT theme_id, libelle FROM theme WHERE theme_id = ?');
        $stmt->execute([$id]);
        $l = $stmt->fetch();

        return $l ? new Theme((int) $l['theme_id'], $l['libelle']) : null;
    }

    /** @return Regime[] */
    public function findRegimes(): array
    {
        $lignes = $this->pdo->query('SELECT regime_id, libelle FROM regime ORDER BY libelle')->fetchAll();

        return array_map(fn(array $l) => new Regime((int) $l['regime_id'], $l['libelle']), $lignes);
    }

    public function findRegimeById(int $id): ?Regime
    {
        $stmt = $this->pdo->prepare('SELECT regime_id, libelle FROM regime WHERE regime_id = ?');
        $stmt->execute([$id]);
        $l = $stmt->fetch();

        return $l ? new Regime((int) $l['regime_id'], $l['libelle']) : null;
    }

    /** @return Allergene[] */
    public function findAllergenes(): array
    {
        $lignes = $this->pdo->query('SELECT allergene_id, libelle FROM allergene ORDER BY libelle')->fetchAll();

        return array_map(fn(array $l) => new Allergene((int) $l['allergene_id'], $l['libelle']), $lignes);
    }
}
