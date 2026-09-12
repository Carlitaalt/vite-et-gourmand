<?php

namespace App\Repository;

use App\Core\MongoConnection;
use App\Entity\StatistiqueMenu;
use DateTimeImmutable;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use stdClass;
use Throwable;

/**
 * Accès aux données NoSQL (MongoDB, collection `commandes_stats`) :
 * un document est enregistré à chaque commande, puis agrégé pour les statistiques de l'administrateur.
 */
class StatistiqueRepository
{
    private const COLLECTION = 'commandes_stats';

    public function enregistrerCommande(int $commandeId, int $menuId, string $menuTitre, float $prixTotal): void
    {
        $manager = MongoConnection::getManager();
        if ($manager === null) {
            return;
        }

        try {
            $ecriture = new BulkWrite();
            $ecriture->insert([
                'commande_id' => $commandeId,
                'menu_id' => $menuId,
                'menu_titre' => $menuTitre,
                'prix_total' => $prixTotal,
                'date' => new UTCDateTime(),
            ]);
            $manager->executeBulkWrite(MongoConnection::getNomBase() . '.' . self::COLLECTION, $ecriture);
        } catch (Throwable $e) {
            // La commande reste valide même si la statistique n'a pas pu être enregistrée
            error_log('[MongoDB écriture] ' . $e->getMessage());
        }
    }

    /**
     * Nombre de commandes par menu (pipeline d'agrégation MongoDB), sur une période optionnelle.
     *
     * @return StatistiqueMenu[]
     */
    public function commandesParMenu(?DateTimeImmutable $debut = null, ?DateTimeImmutable $fin = null): array
    {
        $manager = MongoConnection::getManager();
        if ($manager === null) {
            return [];
        }

        $pipeline = [];

        $filtreDate = [];
        if ($debut !== null) {
            $filtreDate['$gte'] = new UTCDateTime($debut->setTime(0, 0)->getTimestamp() * 1000);
        }
        if ($fin !== null) {
            $filtreDate['$lte'] = new UTCDateTime($fin->setTime(23, 59, 59)->getTimestamp() * 1000);
        }
        if (!empty($filtreDate)) {
            $pipeline[] = ['$match' => ['date' => $filtreDate]];
        }

        $pipeline[] = ['$group' => [
            '_id' => '$menu_id',
            'titre' => ['$last' => '$menu_titre'],
            'nombre' => ['$sum' => 1],
            'montant' => ['$sum' => '$prix_total'],
        ]];
        $pipeline[] = ['$sort' => ['nombre' => -1]];

        try {
            $curseur = $manager->executeCommand(MongoConnection::getNomBase(), new Command([
                'aggregate' => self::COLLECTION,
                'pipeline' => $pipeline,
                'cursor' => new stdClass(),
            ]));
        } catch (Throwable $e) {
            error_log('[MongoDB lecture] ' . $e->getMessage());
            return [];
        }

        $statistiques = [];
        foreach ($curseur as $document) {
            $statistiques[] = new StatistiqueMenu(
                $document->_id !== null ? (int) $document->_id : null,
                (string) $document->titre,
                (int) $document->nombre,
                (float) $document->montant,
            );
        }

        return $statistiques;
    }
}
