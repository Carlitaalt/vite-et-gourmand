<?php

namespace App\Service;

use App\Entity\Menu;
use App\Exception\MetierException;
use App\Repository\MenuRepository;
use App\Repository\ReferenceRepository;

/**
 * Consultation et gestion des menus (création, modification, galerie d'images, suppression).
 */
class MenuService
{
    private const DOSSIER_IMAGES = 'assets/images/menus/';
    private const TAILLE_MAX_IMAGE = 5 * 1024 * 1024;
    /** Types acceptés, déterminés à partir du contenu réel du fichier (et non de son nom). */
    private const TYPES_IMAGE = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function __construct(
        private MenuRepository $menus = new MenuRepository(),
        private ReferenceRepository $references = new ReferenceRepository(),
    ) {
    }

    /**
     * Filtres de la vue globale, reçus en paramètres GET (appel fetch de la page Menus).
     *
     * @return Menu[]
     */
    public function rechercher(array $parametres): array
    {
        $filtres = [];

        foreach (['prixMax' => 'prix_max', 'prixMin' => 'prix_min'] as $cle => $nomParametre) {
            if (isset($parametres[$nomParametre]) && is_numeric($parametres[$nomParametre])) {
                $filtres[$cle] = (float) $parametres[$nomParametre];
            }
        }
        // Le curseur "prix maximum" et le maximum de la fourchette se cumulent : on garde le plus bas
        if (isset($parametres['prix_plafond']) && is_numeric($parametres['prix_plafond'])) {
            $filtres['prixMax'] = min($filtres['prixMax'] ?? INF, (float) $parametres['prix_plafond']);
        }

        foreach (['themeId' => 'theme', 'regimeId' => 'regime', 'personnesMin' => 'personnes'] as $cle => $nomParametre) {
            if (isset($parametres[$nomParametre]) && ctype_digit((string) $parametres[$nomParametre])) {
                $filtres[$cle] = (int) $parametres[$nomParametre];
            }
        }

        return $this->menus->rechercher($filtres);
    }

    public function detailPublic(int $id): ?Menu
    {
        return $this->menus->findById($id, true);
    }

    /** @return Menu[] */
    public function tousLesMenus(): array
    {
        return $this->menus->findAll();
    }

    /** Crée (sans menu_id) ou met à jour un menu, puis ajoute les photos envoyées. */
    public function enregistrer(array $donnees, array $fichiers = []): Menu
    {
        $titre = trim($donnees['titre'] ?? '');
        $prix = (float) ($donnees['prix'] ?? 0);
        $minimum = (int) ($donnees['nombre_personne_minimum'] ?? 0);
        $stock = (int) ($donnees['stock_disponible'] ?? 0);

        if ($titre === '') {
            throw new MetierException('Le titre du menu est obligatoire.');
        }
        if ($prix <= 0 || $minimum < 1 || $stock < 0) {
            throw new MetierException('Vérifiez le prix, le nombre minimum de personnes et le stock.');
        }

        $theme = $this->references->findThemeById((int) ($donnees['theme_id'] ?? 0));
        $regime = $this->references->findRegimeById((int) ($donnees['regime_id'] ?? 0));
        if ($theme === null || $regime === null) {
            throw new MetierException('Choisissez un thème et un régime valides.');
        }

        $id = !empty($donnees['menu_id']) ? (int) $donnees['menu_id'] : null;

        $menu = new Menu(
            $id,
            $titre,
            trim($donnees['description'] ?? ''),
            trim($donnees['conditions'] ?? ''),
            $minimum,
            $prix,
            $theme,
            $regime,
            $stock,
            ($donnees['actif'] ?? '1') === '1',
        );

        if ($id === null) {
            $this->menus->inserer($menu);
        } else {
            $this->menus->mettreAJour($menu);
        }

        if (!empty($fichiers['name'][0])) {
            $this->ajouterImages($menu->getId(), $fichiers);
        }

        return $menu;
    }

    public function supprimer(int $id): void
    {
        if ($this->menus->estCommande($id)) {
            throw new MetierException('Ce menu a déjà été commandé : désactivez-le plutôt que de le supprimer, afin de conserver l\'historique des commandes.');
        }

        $menu = $this->menus->findById($id);
        $this->menus->supprimer($id);

        foreach ($menu?->getImages() ?? [] as $image) {
            $this->supprimerFichier($image->getUrl());
        }
    }

    public function supprimerImage(int $imageId): void
    {
        $url = $this->menus->supprimerImage($imageId);
        if ($url !== null) {
            $this->supprimerFichier($url);
        }
    }

    /**
     * Enregistre les photos envoyées après contrôle du type réel et de la taille.
     * Le nom du fichier est généré par le serveur : l'utilisateur ne choisit ni le nom ni l'extension.
     */
    private function ajouterImages(int $menuId, array $fichiers): void
    {
        $refusees = 0;
        $detecteur = new \finfo(FILEINFO_MIME_TYPE);

        foreach (array_keys($fichiers['name']) as $i) {
            $tmp = $fichiers['tmp_name'][$i] ?? '';
            $type = is_uploaded_file($tmp) ? $detecteur->file($tmp) : false;

            if (($fichiers['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
                || $fichiers['size'][$i] > self::TAILLE_MAX_IMAGE
                || !isset(self::TYPES_IMAGE[$type])
            ) {
                $refusees++;
                continue;
            }

            $nomFichier = 'menu_' . $menuId . '_' . bin2hex(random_bytes(8)) . '.' . self::TYPES_IMAGE[$type];

            if (move_uploaded_file($tmp, $this->racine() . self::DOSSIER_IMAGES . $nomFichier)) {
                $this->menus->ajouterImage($menuId, self::DOSSIER_IMAGES . $nomFichier);
            } else {
                $refusees++;
            }
        }

        if ($refusees > 0) {
            throw new MetierException("Menu enregistré, mais $refusees photo(s) refusée(s) : formats acceptés JPG, PNG ou WEBP, 5 Mo maximum.");
        }
    }

    /** Seules les photos téléversées sont supprimées du disque (jamais les images fournies avec le site). */
    private function supprimerFichier(string $url): void
    {
        $chemin = $this->racine() . $url;
        if (str_starts_with($url, self::DOSSIER_IMAGES) && is_file($chemin)) {
            unlink($chemin);
        }
    }

    private function racine(): string
    {
        return dirname(__DIR__, 2) . '/';
    }
}
