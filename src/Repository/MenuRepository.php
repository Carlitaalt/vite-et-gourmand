<?php

namespace App\Repository;

use App\Entity\Menu;
use App\Entity\MenuImage;
use App\Entity\Regime;
use App\Entity\Theme;

class MenuRepository extends AbstractRepository
{
    private const SELECT = '
        SELECT m.*, t.libelle AS theme_libelle, r.libelle AS regime_libelle
        FROM menu m
        INNER JOIN theme t ON t.theme_id = m.theme_id
        INNER JOIN regime r ON r.regime_id = m.regime_id
    ';

    /**
     * Menus visibles par le public, filtrés selon les critères de la vue globale.
     * Les filtres sont appliqués en SQL avec des paramètres préparés.
     *
     * @param array{prixMax?: float, prixMin?: float, themeId?: int, regimeId?: int, personnesMin?: int} $filtres
     * @return Menu[]
     */
    public function rechercher(array $filtres = []): array
    {
        $conditions = ['m.actif = 1'];
        $parametres = [];

        // Le prix affiché (et filtré) est le prix pour le nombre minimum de personnes
        if (isset($filtres['prixMin'])) {
            $conditions[] = 'm.prix_par_personne * m.nombre_personne_minimum >= ?';
            $parametres[] = $filtres['prixMin'];
        }
        if (isset($filtres['prixMax'])) {
            $conditions[] = 'm.prix_par_personne * m.nombre_personne_minimum <= ?';
            $parametres[] = $filtres['prixMax'];
        }
        if (isset($filtres['themeId'])) {
            $conditions[] = 'm.theme_id = ?';
            $parametres[] = $filtres['themeId'];
        }
        if (isset($filtres['regimeId'])) {
            $conditions[] = 'm.regime_id = ?';
            $parametres[] = $filtres['regimeId'];
        }
        if (isset($filtres['personnesMin'])) {
            $conditions[] = 'm.nombre_personne_minimum >= ?';
            $parametres[] = $filtres['personnesMin'];
        }

        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY m.menu_id');
        $stmt->execute($parametres);

        return $this->hydraterListe($stmt->fetchAll(), false);
    }

    /** Tous les menus, actifs ou non, avec leurs plats (espace employé / administrateur). @return Menu[] */
    public function findAll(): array
    {
        $lignes = $this->pdo->query(self::SELECT . ' ORDER BY m.titre')->fetchAll();

        return $this->hydraterListe($lignes, true);
    }

    public function findById(int $id, bool $actifSeulement = false): ?Menu
    {
        $sql = self::SELECT . ' WHERE m.menu_id = ?' . ($actifSeulement ? ' AND m.actif = 1' : '');
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $ligne = $stmt->fetch();

        return $ligne ? $this->hydraterListe([$ligne], true)[0] : null;
    }

    /**
     * @param int[] $ids
     * @return array<int, Menu> indexés par identifiant
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE m.menu_id IN (' . $this->marqueurs($ids) . ')');
        $stmt->execute(array_values($ids));

        $menus = [];
        foreach ($this->hydraterListe($stmt->fetchAll(), false) as $menu) {
            $menus[$menu->getId()] = $menu;
        }

        return $menus;
    }

    public function inserer(Menu $menu): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO menu (titre, description, conditions, nombre_personne_minimum, prix_par_personne, theme_id, regime_id, stock_disponible, actif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute($this->valeurs($menu));

        $menu->setId((int) $this->pdo->lastInsertId());
    }

    public function mettreAJour(Menu $menu): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE menu
            SET titre = ?, description = ?, conditions = ?, nombre_personne_minimum = ?, prix_par_personne = ?,
                theme_id = ?, regime_id = ?, stock_disponible = ?, actif = ?
            WHERE menu_id = ?
        ');
        $stmt->execute([...$this->valeurs($menu), $menu->getId()]);
    }

    public function supprimer(int $id): void
    {
        // Les images et les liaisons menu_plat sont supprimées en cascade (ON DELETE CASCADE)
        $this->pdo->prepare('DELETE FROM menu WHERE menu_id = ?')->execute([$id]);
    }

    public function estCommande(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM commande_menu WHERE menu_id = ?');
        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** Retire une commande du stock. Renvoie false si le stock était déjà épuisé. */
    public function decrementerStock(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE menu SET stock_disponible = stock_disponible - 1 WHERE menu_id = ? AND stock_disponible > 0');
        $stmt->execute([$id]);

        return $stmt->rowCount() === 1;
    }

    public function incrementerStock(int $id): void
    {
        $this->pdo->prepare('UPDATE menu SET stock_disponible = stock_disponible + 1 WHERE menu_id = ?')->execute([$id]);
    }

    public function ajouterImage(int $menuId, string $url): void
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(MAX(ordre), 0) + 1 FROM menu_image WHERE menu_id = ?');
        $stmt->execute([$menuId]);
        $ordre = (int) $stmt->fetchColumn();

        $this->pdo->prepare('INSERT INTO menu_image (menu_id, url, ordre) VALUES (?, ?, ?)')->execute([$menuId, $url, $ordre]);
    }

    /** Supprime une image et renvoie son chemin (pour effacer le fichier), ou null si elle n'existe pas. */
    public function supprimerImage(int $imageId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT url FROM menu_image WHERE image_id = ?');
        $stmt->execute([$imageId]);
        $url = $stmt->fetchColumn();

        if ($url === false) {
            return null;
        }

        $this->pdo->prepare('DELETE FROM menu_image WHERE image_id = ?')->execute([$imageId]);

        return $url;
    }

    private function valeurs(Menu $menu): array
    {
        return [
            $menu->getTitre(),
            $menu->getDescription(),
            $menu->getConditions(),
            $menu->getNombrePersonneMinimum(),
            $menu->getPrixParPersonne(),
            $menu->getTheme()->getId(),
            $menu->getRegime()->getId(),
            $menu->getStockDisponible(),
            (int) $menu->estActif(),
        ];
    }

    /**
     * Transforme les lignes SQL en objets Menu, puis charge leurs images (et leurs plats si demandé)
     * en une seule requête pour tous les menus (évite une requête par menu).
     *
     * @return Menu[]
     */
    private function hydraterListe(array $lignes, bool $avecPlats): array
    {
        $menus = [];
        foreach ($lignes as $l) {
            $menus[(int) $l['menu_id']] = new Menu(
                (int) $l['menu_id'],
                $l['titre'],
                $l['description'] ?? '',
                $l['conditions'] ?? '',
                (int) $l['nombre_personne_minimum'],
                (float) $l['prix_par_personne'],
                new Theme((int) $l['theme_id'], $l['theme_libelle']),
                new Regime((int) $l['regime_id'], $l['regime_libelle']),
                (int) $l['stock_disponible'],
                (bool) $l['actif'],
            );
        }

        if (empty($menus)) {
            return [];
        }

        $ids = array_keys($menus);

        $stmt = $this->pdo->prepare('SELECT * FROM menu_image WHERE menu_id IN (' . $this->marqueurs($ids) . ') ORDER BY ordre, image_id');
        $stmt->execute($ids);
        $images = [];
        foreach ($stmt->fetchAll() as $l) {
            $images[(int) $l['menu_id']][] = new MenuImage((int) $l['image_id'], $l['url'], (int) $l['ordre']);
        }

        $plats = $avecPlats ? (new PlatRepository($this->pdo))->findByMenuIds($ids) : [];

        foreach ($menus as $id => $menu) {
            $menu->setImages($images[$id] ?? []);
            $menu->setPlats($plats[$id] ?? []);
        }

        return array_values($menus);
    }
}
