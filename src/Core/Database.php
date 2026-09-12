<?php

namespace App\Core;

use PDO;
use Throwable;

/**
 * Connexion unique à MySQL (pattern Singleton).
 * Les identifiants viennent des variables d'environnement : aucun secret n'est écrit dans le code.
 */
final class Database
{
    private static ?PDO $connexion = null;

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            $hote = getenv('DB_HOST') ?: 'mysql';
            $port = getenv('DB_PORT') ?: '3306';
            $base = getenv('DB_NAME');

            self::$connexion = new PDO(
                "mysql:host=$hote;port=$port;dbname=$base;charset=utf8mb4",
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Vraies requêtes préparées côté MySQL (pas d'émulation par PDO)
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$connexion;
    }

    /**
     * Exécute $operation dans une transaction : soit tout est validé, soit tout est annulé.
     */
    public static function transaction(callable $operation): mixed
    {
        $pdo = self::getConnexion();
        $pdo->beginTransaction();

        try {
            $resultat = $operation();
            $pdo->commit();
            return $resultat;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
