<?php

namespace App\Repository;

use App\Entity\Allergene;
use App\Entity\Plat;

class PlatRepository extends AbstractRepository
{
    /** @return Plat[] */
    public function findAll(): array
    {
        $lignes = $this->pdo->query('SELECT * FROM plat ORDER BY categorie, titre_plat')->fetchAll();

        return array_values($this->hydraterListe($lignes));
    }

    public function findById(int $id): ?Plat
    {
        $stmt = $this->pdo->prepare('SELECT * FROM plat WHERE plat_id = ?');
        $stmt->execute([$id]);
        $ligne = $stmt->fetch();

        return $ligne ? array_values($this->hydraterListe([$ligne]))[0] : null;
    }

    /**
     * Plats de plusieurs menus en une seule requête.
     *
     * @param int[] $menuIds
     * @return array<int, Plat[]> plats indexés par menu_id
     */
    public function findByMenuIds(array $menuIds): array
    {
        if (empty($menuIds)) {
            return [];
        }

        $stmt = $this->pdo->prepare('
            SELECT p.*, mp.menu_id AS menu_lie
            FROM plat p
            INNER JOIN menu_plat mp ON mp.plat_id = p.plat_id
            WHERE mp.menu_id IN (' . $this->marqueurs($menuIds) . ')
            ORDER BY p.titre_plat
        ');
        $stmt->execute(array_values($menuIds));
        $lignes = $stmt->fetchAll();

        $plats = $this->hydraterListe($lignes);

        $parMenu = [];
        foreach ($lignes as $l) {
            $parMenu[(int) $l['menu_lie']][] = $plats[(int) $l['plat_id']];
        }

        return $parMenu;
    }

    /**
     * Crée ou met à jour un plat avec ses menus et ses allergènes. Renvoie l'identifiant du plat.
     * À appeler dans une transaction (voir PlatService).
     *
     * @param int[] $menuIds
     * @param int[] $allergeneIds
     */
    public function enregistrer(Plat $plat, array $menuIds, array $allergeneIds): int
    {
        $valeurs = [$plat->getTitre(), $plat->getDescription(), $plat->getCategorie(), (int) $plat->estActif()];

        if ($plat->getId() === null) {
            $this->pdo->prepare('INSERT INTO plat (titre_plat, description, categorie, actif) VALUES (?, ?, ?, ?)')->execute($valeurs);
            $platId = (int) $this->pdo->lastInsertId();
        } else {
            $platId = $plat->getId();
            $this->pdo->prepare('UPDATE plat SET titre_plat = ?, description = ?, categorie = ?, actif = ? WHERE plat_id = ?')
                ->execute([...$valeurs, $platId]);
        }

        // Tables de liaison : on remplace les anciennes associations par les nouvelles
        $this->pdo->prepare('DELETE FROM menu_plat WHERE plat_id = ?')->execute([$platId]);
        $lienMenu = $this->pdo->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)');
        foreach ($menuIds as $menuId) {
            $lienMenu->execute([$menuId, $platId]);
        }

        $this->pdo->prepare('DELETE FROM plat_allergene WHERE plat_id = ?')->execute([$platId]);
        $lienAllergene = $this->pdo->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (?, ?)');
        foreach ($allergeneIds as $allergeneId) {
            $lienAllergene->execute([$platId, $allergeneId]);
        }

        return $platId;
    }

    public function supprimer(int $id): void
    {
        // Les liaisons menu_plat et plat_allergene sont supprimées en cascade
        $this->pdo->prepare('DELETE FROM plat WHERE plat_id = ?')->execute([$id]);
    }

    /**
     * @return array<int, Plat> indexés par plat_id, avec allergènes et menus chargés
     */
    private function hydraterListe(array $lignes): array
    {
        $plats = [];
        foreach ($lignes as $l) {
            $plats[(int) $l['plat_id']] ??= new Plat(
                (int) $l['plat_id'],
                $l['titre_plat'],
                $l['description'] ?? '',
                $l['categorie'],
                (bool) $l['actif'],
            );
        }

        if (empty($plats)) {
            return [];
        }

        $ids = array_keys($plats);

        $stmt = $this->pdo->prepare('
            SELECT pa.plat_id, a.allergene_id, a.libelle
            FROM plat_allergene pa
            INNER JOIN allergene a ON a.allergene_id = pa.allergene_id
            WHERE pa.plat_id IN (' . $this->marqueurs($ids) . ')
            ORDER BY a.libelle
        ');
        $stmt->execute($ids);
        $allergenes = [];
        foreach ($stmt->fetchAll() as $l) {
            $allergenes[(int) $l['plat_id']][] = new Allergene((int) $l['allergene_id'], $l['libelle']);
        }

        $stmt = $this->pdo->prepare('
            SELECT mp.plat_id, m.menu_id, m.titre
            FROM menu_plat mp
            INNER JOIN menu m ON m.menu_id = mp.menu_id
            WHERE mp.plat_id IN (' . $this->marqueurs($ids) . ')
            ORDER BY m.titre
        ');
        $stmt->execute($ids);
        $menus = [];
        foreach ($stmt->fetchAll() as $l) {
            $menus[(int) $l['plat_id']][(int) $l['menu_id']] = $l['titre'];
        }

        foreach ($plats as $id => $plat) {
            $plat->setAllergenes($allergenes[$id] ?? []);
            $plat->setMenus($menus[$id] ?? []);
        }

        return $plats;
    }
}
