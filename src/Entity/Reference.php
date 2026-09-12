<?php

namespace App\Entity;

/**
 * Donnée de référence simple (identifiant + libellé) : thème, régime, allergène.
 * Classe abstraite : on ne manipule que ses classes filles (héritage).
 */
abstract class Reference
{
    public function __construct(
        private int $id,
        private string $libelle,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /** Libellé sans accents ni espaces, utilisable dans une classe CSS (ex : "Végétarien" → "vegetarien"). */
    public function getSlug(): string
    {
        $sansAccents = strtr(mb_strtolower($this->libelle), [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c',
        ]);

        return trim(preg_replace('/[^a-z0-9]+/', '-', $sansAccents), '-');
    }
}
