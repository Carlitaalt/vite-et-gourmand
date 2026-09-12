<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\EtapeSuivi;
use App\Entity\StatistiqueMenu;
use App\Enum\StatutCommande;
use DateTimeImmutable;

class CommandeRepository extends AbstractRepository
{
    private const SELECT = '
        SELECT c.*, cm.menu_id, cm.prix_unitaire
        FROM commande c
        INNER JOIN commande_menu cm ON cm.commande_id = c.commande_id
    ';

    public function findById(int $id): ?Commande
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE c.commande_id = ?');
        $stmt->execute([$id]);

        return $this->hydraterListe($stmt->fetchAll())[0] ?? null;
    }

    /** Commandes d'un client, de la plus récente à la plus ancienne. @return Commande[] */
    public function findByUtilisateur(int $utilisateurId): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE c.utilisateur_id = ? ORDER BY c.date_commande DESC');
        $stmt->execute([$utilisateurId]);

        return $this->hydraterListe($stmt->fetchAll());
    }

    /** Toutes les commandes (espace employé / administrateur). @return Commande[] */
    public function findAll(): array
    {
        $lignes = $this->pdo->query(self::SELECT . ' ORDER BY c.date_prestation DESC, c.commande_id DESC')->fetchAll();

        return $this->hydraterListe($lignes);
    }

    public function aDesCommandes(int $utilisateurId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM commande WHERE utilisateur_id = ?');
        $stmt->execute([$utilisateurId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** Enregistre une nouvelle commande, sa ligne de menu et la première étape du suivi. */
    public function inserer(Commande $commande): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO commande (utilisateur_id, statut_id, date_prestation, heure_livraison, adresse_livraison, ville_livraison,
                                  distance_km, nombre_personnes, prix_total, prix_livraison, pret_materiel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $commande->getUtilisateurId(),
            $commande->getStatut()->value,
            $commande->getDatePrestation()->format('Y-m-d'),
            $commande->getHeureLivraison(),
            $commande->getAdresseLivraison(),
            $commande->getVilleLivraison(),
            $commande->getDistanceKm(),
            $commande->getNombrePersonnes(),
            $commande->getPrixTotal(),
            $commande->getPrixLivraison(),
            (int) $commande->aPretMateriel(),
        ]);
        $commande->setId((int) $this->pdo->lastInsertId());

        $this->pdo->prepare('INSERT INTO commande_menu (commande_id, menu_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)')
            ->execute([$commande->getId(), $commande->getMenuId(), $commande->getNombrePersonnes(), $commande->getPrixUnitaire()]);

        $this->ajouterEtapeSuivi($commande);
    }

    /** Enregistre la modification de la prestation par le client. */
    public function mettreAJourPrestation(Commande $commande): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE commande
            SET date_prestation = ?, heure_livraison = ?, adresse_livraison = ?, ville_livraison = ?, distance_km = ?,
                nombre_personnes = ?, prix_livraison = ?, prix_total = ?
            WHERE commande_id = ?
        ');
        $stmt->execute([
            $commande->getDatePrestation()->format('Y-m-d'),
            $commande->getHeureLivraison(),
            $commande->getAdresseLivraison(),
            $commande->getVilleLivraison(),
            $commande->getDistanceKm(),
            $commande->getNombrePersonnes(),
            $commande->getPrixLivraison(),
            $commande->getPrixTotal(),
            $commande->getId(),
        ]);

        $this->pdo->prepare('UPDATE commande_menu SET quantite = ? WHERE commande_id = ?')
            ->execute([$commande->getNombrePersonnes(), $commande->getId()]);
    }

    /** Enregistre le nouveau statut (et le motif d'annulation éventuel) puis l'ajoute à l'historique. */
    public function enregistrerStatut(Commande $commande): void
    {
        $stmt = $this->pdo->prepare('UPDATE commande SET statut_id = ?, motif_annulation = ?, mode_contact = ? WHERE commande_id = ?');
        $stmt->execute([
            $commande->getStatut()->value,
            $commande->getMotifAnnulation(),
            $commande->getModeContact(),
            $commande->getId(),
        ]);

        $this->ajouterEtapeSuivi($commande);
    }

    /**
     * Chiffre d'affaires des commandes terminées, par menu, avec filtres optionnels.
     *
     * @return StatistiqueMenu[]
     */
    public function chiffreAffairesParMenu(?int $menuId, ?DateTimeImmutable $debut, ?DateTimeImmutable $fin): array
    {
        $conditions = ['c.statut_id = ?'];
        $parametres = [StatutCommande::Terminee->value];

        if ($menuId !== null) {
            $conditions[] = 'm.menu_id = ?';
            $parametres[] = $menuId;
        }
        if ($debut !== null) {
            $conditions[] = 'c.date_commande >= ?';
            $parametres[] = $debut->format('Y-m-d 00:00:00');
        }
        if ($fin !== null) {
            $conditions[] = 'c.date_commande <= ?';
            $parametres[] = $fin->format('Y-m-d 23:59:59');
        }

        $stmt = $this->pdo->prepare('
            SELECT m.menu_id, m.titre, COUNT(*) AS nombre, SUM(c.prix_total) AS chiffre_affaires
            FROM commande c
            INNER JOIN commande_menu cm ON cm.commande_id = c.commande_id
            INNER JOIN menu m ON m.menu_id = cm.menu_id
            WHERE ' . implode(' AND ', $conditions) . '
            GROUP BY m.menu_id, m.titre
            ORDER BY chiffre_affaires DESC
        ');
        $stmt->execute($parametres);

        return array_map(
            fn(array $l) => new StatistiqueMenu((int) $l['menu_id'], $l['titre'], (int) $l['nombre'], (float) $l['chiffre_affaires']),
            $stmt->fetchAll()
        );
    }

    private function ajouterEtapeSuivi(Commande $commande): void
    {
        $this->pdo->prepare('INSERT INTO commande_statut (commande_id, statut_id, date_modification) VALUES (?, ?, NOW())')
            ->execute([$commande->getId(), $commande->getStatut()->value]);
    }

    /**
     * Transforme les lignes en objets Commande, puis charge en une requête chacun
     * l'historique, les avis, les clients et les menus de toutes les commandes.
     *
     * @return Commande[]
     */
    private function hydraterListe(array $lignes): array
    {
        $commandes = [];
        foreach ($lignes as $l) {
            $commandes[(int) $l['commande_id']] ??= new Commande(
                (int) $l['commande_id'],
                (int) $l['utilisateur_id'],
                (int) $l['menu_id'],
                StatutCommande::from((int) $l['statut_id']),
                new DateTimeImmutable($l['date_prestation']),
                $l['heure_livraison'],
                $l['adresse_livraison'],
                $l['ville_livraison'],
                (float) ($l['distance_km'] ?? 0),
                (int) $l['nombre_personnes'],
                (float) $l['prix_unitaire'],
                (float) $l['prix_livraison'],
                (float) $l['prix_total'],
                (bool) $l['pret_materiel'],
                $this->date($l['date_commande']),
                $l['motif_annulation'],
                $l['mode_contact'],
            );
        }

        if (empty($commandes)) {
            return [];
        }

        $ids = array_keys($commandes);

        $stmt = $this->pdo->prepare('
            SELECT commande_id, statut_id, date_modification
            FROM commande_statut
            WHERE commande_id IN (' . $this->marqueurs($ids) . ')
            ORDER BY date_modification, commande_statut_id
        ');
        $stmt->execute($ids);
        $historiques = [];
        foreach ($stmt->fetchAll() as $l) {
            $historiques[(int) $l['commande_id']][] = new EtapeSuivi(
                StatutCommande::from((int) $l['statut_id']),
                new DateTimeImmutable($l['date_modification'])
            );
        }

        $avis = (new AvisRepository($this->pdo))->findByCommandeIds($ids);
        $clients = (new UtilisateurRepository($this->pdo))->findByIds(array_unique(array_map(fn(Commande $c) => $c->getUtilisateurId(), $commandes)));
        $menus = (new MenuRepository($this->pdo))->findByIds(array_unique(array_map(fn(Commande $c) => $c->getMenuId(), $commandes)));

        foreach ($commandes as $id => $commande) {
            $commande->setHistorique($historiques[$id] ?? []);
            $commande->setAvis($avis[$id] ?? null);
            $commande->setClient($clients[$commande->getUtilisateurId()] ?? null);
            $commande->setMenu($menus[$commande->getMenuId()] ?? null);
        }

        return array_values($commandes);
    }
}
