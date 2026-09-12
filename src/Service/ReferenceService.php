<?php

namespace App\Service;

use App\Entity\Allergene;
use App\Entity\Regime;
use App\Entity\Theme;
use App\Repository\ReferenceRepository;

/**
 * Listes de référence utilisées par les filtres et les formulaires (thèmes, régimes, allergènes).
 */
class ReferenceService
{
    public function __construct(private ReferenceRepository $references = new ReferenceRepository())
    {
    }

    /** @return Theme[] */
    public function themes(): array
    {
        return $this->references->findThemes();
    }

    /** @return Regime[] */
    public function regimes(): array
    {
        return $this->references->findRegimes();
    }

    /** @return Allergene[] */
    public function allergenes(): array
    {
        return $this->references->findAllergenes();
    }
}
