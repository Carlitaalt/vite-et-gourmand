<?php

namespace App\Repository;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

/**
 * Classe mère des repositories : chaque repository gère l'accès aux données d'une entité.
 * Toutes les requêtes sont préparées (protection contre les injections SQL)
 * et les lignes SQL sont transformées en objets entité ("hydratation").
 */
abstract class AbstractRepository
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnexion();
    }

    /** Génère "?, ?, ?" pour une clause IN (...) avec autant de marqueurs que de valeurs. */
    protected function marqueurs(array $valeurs): string
    {
        return implode(', ', array_fill(0, count($valeurs), '?'));
    }

    protected function date(?string $valeur): ?DateTimeImmutable
    {
        return $valeur ? new DateTimeImmutable($valeur) : null;
    }
}
