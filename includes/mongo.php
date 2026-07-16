<?php

$mongoUri = getenv('MONGO_URI') ?: 'mongodb://mongo:27017';

try {
    $mongoClient = new MongoDB\Driver\Manager($mongoUri);
} catch (Exception $e) {
    error_log('[MongoDB] ' . $e->getMessage());
    $mongoClient = null;
}

/**
 * Enregistre une commande dans MongoDB pour les statistiques admin
 */
function enregistrerCommandeMongo($mongoClient, int $menuId, string $menuTitre, float $prixTotal): void {
    if (!$mongoClient) return;

    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert([
            'menu_id' => $menuId,
            'menu_titre' => $menuTitre,
            'prix_total' => $prixTotal,
            'date' => new MongoDB\BSON\UTCDateTime()
        ]);
        $mongoClient->executeBulkWrite('vite_gourmand.commandes_stats', $bulk);
    } catch (Exception $e) {
        error_log('[MongoDB write] ' . $e->getMessage());
    }
}