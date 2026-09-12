<?php

namespace App\Service;

use App\Entity\Avis;
use App\Enum\StatutAvis;
use App\Exception\MetierException;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;

class AvisService
{
    public function __construct(
        private AvisRepository $avis = new AvisRepository(),
        private CommandeRepository $commandes = new CommandeRepository(),
    ) {
    }

    /** @return Avis[] */
    public function avisPublies(int $limite = 6): array
    {
        return $this->avis->findPublies($limite);
    }

    /** @return Avis[] */
    public function tousLesAvis(): array
    {
        return $this->avis->findAll();
    }

    /** Un client ne peut noter que ses propres commandes terminées, une seule fois. */
    public function deposer(int $clientId, int $commandeId, int $note, string $commentaire): void
    {
        $commande = $this->commandes->findById($commandeId);

        if ($commande === null || $commande->getUtilisateurId() !== $clientId) {
            throw new MetierException('Commande introuvable.');
        }
        if (!$commande->peutRecevoirAvis()) {
            throw new MetierException('Vous pouvez donner votre avis une seule fois, sur une commande terminée.');
        }
        if (trim($commentaire) === '') {
            throw new MetierException('Merci d\'ajouter un commentaire à votre note.');
        }

        $this->avis->inserer(new Avis(null, $commandeId, $clientId, $note, trim($commentaire)));
    }

    /** Validation (publication sur l'accueil) ou refus d'un avis par un employé. */
    public function moderer(int $avisId, bool $publier): StatutAvis
    {
        $statut = $publier ? StatutAvis::Publie : StatutAvis::Refuse;

        if (!$this->avis->changerStatut($avisId, $statut)) {
            throw new MetierException('Avis introuvable.');
        }

        return $statut;
    }
}
