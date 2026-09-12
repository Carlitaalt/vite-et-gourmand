<?php

namespace App\Entity;

use JsonSerializable;

/**
 * Menu proposé par l'entreprise (table `menu`), avec sa galerie et ses plats.
 * JsonSerializable : l'objet peut être renvoyé directement en JSON par l'API (appels fetch).
 */
class Menu implements JsonSerializable
{
    private const ORDRE_CATEGORIES = ['Entrée', 'Plat', 'Fromage', 'Dessert', 'Boisson'];

    /** @var MenuImage[] */
    private array $images = [];

    /** @var Plat[] */
    private array $plats = [];

    public function __construct(
        private ?int $id,
        private string $titre,
        private string $description,
        private string $conditions,
        private int $nombrePersonneMinimum,
        private float $prixParPersonne,
        private Theme $theme,
        private Regime $regime,
        private int $stockDisponible,
        private bool $actif = true,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getConditions(): string
    {
        return $this->conditions;
    }

    public function getNombrePersonneMinimum(): int
    {
        return $this->nombrePersonneMinimum;
    }

    public function getPrixParPersonne(): float
    {
        return $this->prixParPersonne;
    }

    /** Prix affiché dans la vue globale : prix pour le nombre minimum de personnes. */
    public function getPrixMinimum(): float
    {
        return $this->prixParPersonne * $this->nombrePersonneMinimum;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function getRegime(): Regime
    {
        return $this->regime;
    }

    public function getStockDisponible(): int
    {
        return $this->stockDisponible;
    }

    public function estActif(): bool
    {
        return $this->actif;
    }

    public function estDisponible(): bool
    {
        return $this->actif && $this->stockDisponible > 0;
    }

    /** @return MenuImage[] */
    public function getImages(): array
    {
        return $this->images;
    }

    /** @param MenuImage[] $images */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function getImagePrincipale(): ?string
    {
        return isset($this->images[0]) ? $this->images[0]->getUrl() : null;
    }

    /** @return Plat[] */
    public function getPlats(): array
    {
        return $this->plats;
    }

    /** @param Plat[] $plats */
    public function setPlats(array $plats): void
    {
        $this->plats = $plats;
    }

    public function getNombrePlats(): int
    {
        return count($this->plats);
    }

    /**
     * Plats regroupés par catégorie, dans l'ordre d'un repas (entrée, plat, dessert...).
     *
     * @return array<string, Plat[]>
     */
    public function getPlatsParCategorie(): array
    {
        $groupes = [];
        foreach ($this->plats as $plat) {
            if ($plat->estActif()) {
                $groupes[$plat->getCategorie()][] = $plat;
            }
        }

        uksort($groupes, function (string $a, string $b): int {
            $rangA = array_search($a, self::ORDRE_CATEGORIES, true);
            $rangB = array_search($b, self::ORDRE_CATEGORIES, true);
            return ($rangA === false ? 99 : $rangA) <=> ($rangB === false ? 99 : $rangB);
        });

        return $groupes;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'nombrePersonneMinimum' => $this->nombrePersonneMinimum,
            'prixParPersonne' => $this->prixParPersonne,
            'prixMinimum' => $this->getPrixMinimum(),
            'themeId' => $this->theme->getId(),
            'theme' => $this->theme->getLibelle(),
            'regime' => $this->regime->getLibelle(),
            'regimeSlug' => $this->regime->getSlug(),
            'stockDisponible' => $this->stockDisponible,
            'image' => $this->getImagePrincipale(),
        ];
    }
}
