// Données de démonstration MongoDB (statistiques de l'administrateur).
// Exécuté automatiquement au premier démarrage du conteneur mongo (docker-entrypoint-initdb.d),
// ou manuellement en production : mongosh "<MONGO_URL>" database/mongo-init.js
// Chaque document correspond à une commande de database/seed.sql.

db = db.getSiblingDB('vite_gourmand');

if (db.commandes_stats.countDocuments() === 0) {
    db.commandes_stats.insertMany([
        { commande_id: 1, menu_id: 1, menu_titre: 'Menu Classique', prix_total: 420.0, date: new Date('2026-06-01T10:00:00Z') },
        { commande_id: 2, menu_id: 3, menu_titre: 'Menu de Noël', prix_total: 550.0, date: new Date('2026-06-20T14:30:00Z') },
        { commande_id: 3, menu_id: 5, menu_titre: 'Menu Prestige', prix_total: 985.9, date: new Date('2026-09-01T09:15:00Z') },
    ]);
}
