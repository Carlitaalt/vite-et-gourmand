<?php

namespace App\Entity;

use JsonSerializable;

/**
 * Ligne de statistiques pour un menu : nombre de commandes et chiffre d'affaires (espace administrateur).
 */
final class StatistiqueMenu implements JsonSerializable
{
    public function __construct(
        private ?int $menuId,
        private string $titre,
        private int $nombreCommandes,
        private float $chiffreAffaires,
    ) {
    }

    public function getMenuId(): ?int
    {
        return $this->menuId;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getNombreCommandes(): int
    {
        return $this->nombreCommandes;
    }

    public function getChiffreAffaires(): float
    {
        return $this->chiffreAffaires;
    }

    public function jsonSerialize(): array
    {
        return [
            'menuId' => $this->menuId,
            'titre' => $this->titre,
            'nombreCommandes' => $this->nombreCommandes,
            'chiffreAffaires' => round($this->chiffreAffaires, 2),
        ];
    }
}
