<?php

namespace App\Service;

use App\Core\Database;
use App\Entity\Plat;
use App\Exception\MetierException;
use App\Repository\PlatRepository;

class PlatService
{
    public function __construct(private PlatRepository $plats = new PlatRepository())
    {
    }

    /** @return Plat[] */
    public function tousLesPlats(): array
    {
        return $this->plats->findAll();
    }

    /** Crée ou modifie un plat ; un même plat peut être rattaché à plusieurs menus. */
    public function enregistrer(array $donnees): void
    {
        $titre = trim($donnees['titre_plat'] ?? '');
        $categorie = $donnees['categorie'] ?? '';

        if ($titre === '') {
            throw new MetierException('Le nom du plat est obligatoire.');
        }
        if (!in_array($categorie, Plat::CATEGORIES, true)) {
            throw new MetierException('Catégorie de plat invalide.');
        }

        $plat = new Plat(
            !empty($donnees['plat_id']) ? (int) $donnees['plat_id'] : null,
            $titre,
            trim($donnees['description'] ?? ''),
            $categorie,
            ($donnees['actif'] ?? '1') === '1',
        );

        $menuIds = array_map('intval', (array) ($donnees['menus'] ?? []));
        $allergeneIds = array_map('intval', (array) ($donnees['allergenes'] ?? []));

        Database::transaction(fn() => $this->plats->enregistrer($plat, $menuIds, $allergeneIds));
    }

    public function supprimer(int $id): void
    {
        $this->plats->supprimer($id);
    }
}
