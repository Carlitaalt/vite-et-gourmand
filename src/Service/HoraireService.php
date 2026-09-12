<?php

namespace App\Service;

use App\Core\Database;
use App\Entity\Horaire;
use App\Exception\MetierException;
use App\Repository\HoraireRepository;

class HoraireService
{
    public function __construct(private HoraireRepository $horaires = new HoraireRepository())
    {
    }

    /** @return Horaire[] */
    public function tousLesHoraires(): array
    {
        return $this->horaires->findAll();
    }

    /**
     * Tableaux reçus du formulaire, indexés par horaire_id : ouvert[id], debut[id], fin[id].
     */
    public function mettreAJour(array $ouvert, array $debut, array $fin): void
    {
        $modifications = [];

        foreach ($this->horaires->findAll() as $horaire) {
            $id = $horaire->getId();

            if (!isset($ouvert[$id])) {
                $modifications[$id] = [Horaire::FERME, Horaire::FERME];
                continue;
            }

            $ouverture = $this->heure($debut[$id] ?? '');
            $fermeture = $this->heure($fin[$id] ?? '');

            if ($ouverture === null || $fermeture === null || $ouverture >= $fermeture) {
                throw new MetierException("{$horaire->getJour()} : l'heure de fermeture doit être après l'heure d'ouverture.");
            }

            $modifications[$id] = [$ouverture, $fermeture];
        }

        Database::transaction(function () use ($modifications): void {
            foreach ($modifications as $id => [$ouverture, $fermeture]) {
                $this->horaires->mettreAJour($id, $ouverture, $fermeture);
            }
        });
    }

    private function heure(string $valeur): ?string
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valeur) ? $valeur . ':00' : null;
    }
}
