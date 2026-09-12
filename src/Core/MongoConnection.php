<?php

namespace App\Core;

use MongoDB\Driver\Manager;
use Throwable;

/**
 * Connexion unique à MongoDB (base NoSQL utilisée pour les statistiques de commandes).
 * Si MongoDB est indisponible, l'application continue de fonctionner sans les statistiques.
 */
final class MongoConnection
{
    private static ?Manager $manager = null;

    public static function getManager(): ?Manager
    {
        if (self::$manager === null && class_exists(Manager::class)) {
            try {
                self::$manager = new Manager(getenv('MONGO_URI') ?: 'mongodb://mongo:27017');
            } catch (Throwable $e) {
                error_log('[MongoDB] ' . $e->getMessage());
            }
        }

        return self::$manager;
    }

    public static function getNomBase(): string
    {
        return getenv('MONGO_DB') ?: 'vite_gourmand';
    }
}
