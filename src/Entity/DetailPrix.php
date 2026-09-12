<?php

namespace App\Entity;

use JsonSerializable;

/**
 * Détail du prix d'une commande (objet valeur non persisté) : calculé par CommandeService,
 * affiché dans le récapitulatif avant validation et renvoyé en JSON à l'appel fetch.
 */
final class DetailPrix implements JsonSerializable
{
    public function __construct(
        public readonly float $prixParPersonne,
        public readonly int $nombrePersonnes,
        public readonly float $sousTotal,
        public readonly float $remise,
        public readonly float $fraisLivraison,
    ) {
    }

    public function getTotal(): float
    {
        return round($this->sousTotal - $this->remise + $this->fraisLivraison, 2);
    }

    public function jsonSerialize(): array
    {
        return [
            'prixParPersonne' => $this->prixParPersonne,
            'nombrePersonnes' => $this->nombrePersonnes,
            'sousTotal' => $this->sousTotal,
            'remise' => $this->remise,
            'fraisLivraison' => $this->fraisLivraison,
            'total' => $this->getTotal(),
        ];
    }
}
