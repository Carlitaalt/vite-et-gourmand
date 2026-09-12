<?php

namespace App\Entity;

/**
 * Horaires d'ouverture d'un jour de la semaine (table `horaire`).
 * Un jour fermé est enregistré avec 00:00:00 en ouverture et en fermeture.
 */
class Horaire
{
    public const FERME = '00:00:00';

    public function __construct(
        private int $id,
        private string $jour,
        private string $heureOuverture,
        private string $heureFermeture,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getJour(): string
    {
        return $this->jour;
    }

    public function estOuvert(): bool
    {
        return $this->heureOuverture !== self::FERME;
    }

    /** Heure au format HH:MM (pour les champs <input type="time">). */
    public function getOuverture(): string
    {
        return substr($this->heureOuverture, 0, 5);
    }

    public function getFermeture(): string
    {
        return substr($this->heureFermeture, 0, 5);
    }

    /** Ex : "09h00 - 18h00" ou "Fermé". */
    public function getPlageAffichee(): string
    {
        if (!$this->estOuvert()) {
            return 'Fermé';
        }

        return str_replace(':', 'h', $this->getOuverture()) . ' - ' . str_replace(':', 'h', $this->getFermeture());
    }
}
