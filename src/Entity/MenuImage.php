<?php

namespace App\Entity;

/** Photo de la galerie d'un menu (table `menu_image`). */
class MenuImage
{
    public function __construct(
        private int $id,
        private string $url,
        private int $ordre,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }
}
