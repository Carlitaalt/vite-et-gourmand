<?php

namespace App\Entity;

/**
 * Entrée, plat ou dessert (table `plat`). Un plat peut appartenir à plusieurs menus (table `menu_plat`).
 */
class Plat
{
    public const CATEGORIES = ['Entrée', 'Plat', 'Dessert', 'Boisson'];

    /** @var Allergene[] */
    private array $allergenes = [];

    /** @var array<int, string> titres des menus indexés par menu_id */
    private array $menus = [];

    public function __construct(
        private ?int $id,
        private string $titre,
        private string $description,
        private string $categorie,
        private bool $actif = true,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function estActif(): bool
    {
        return $this->actif;
    }

    /** @return Allergene[] */
    public function getAllergenes(): array
    {
        return $this->allergenes;
    }

    /** @param Allergene[] $allergenes */
    public function setAllergenes(array $allergenes): void
    {
        $this->allergenes = $allergenes;
    }

    /** @return int[] */
    public function getAllergeneIds(): array
    {
        return array_map(fn(Allergene $a) => $a->getId(), $this->allergenes);
    }

    public function getAllergenesTexte(): string
    {
        return implode(', ', array_map(fn(Allergene $a) => $a->getLibelle(), $this->allergenes));
    }

    /** @return array<int, string> */
    public function getMenus(): array
    {
        return $this->menus;
    }

    /** @param array<int, string> $menus */
    public function setMenus(array $menus): void
    {
        $this->menus = $menus;
    }

    /** @return int[] */
    public function getMenuIds(): array
    {
        return array_keys($this->menus);
    }

    public function getTitresMenus(): string
    {
        return implode(', ', $this->menus);
    }
}
