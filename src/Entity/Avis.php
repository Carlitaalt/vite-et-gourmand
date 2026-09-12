<?php

namespace App\Entity;

use App\Enum\StatutAvis;
use App\Exception\MetierException;
use DateTimeImmutable;

/**
 * Avis laissé par un client sur une commande terminée (table `avis`).
 * Il n'apparaît sur la page d'accueil qu'après validation par un employé.
 */
class Avis
{
    public function __construct(
        private ?int $id,
        private int $commandeId,
        private int $utilisateurId,
        private int $note,
        private string $commentaire,
        private StatutAvis $statut = StatutAvis::EnAttente,
        private ?DateTimeImmutable $creeLe = null,
        // Informations d'affichage chargées par jointure
        private ?string $auteurPrenom = null,
        private ?string $auteurNom = null,
        private ?string $menuTitre = null,
    ) {
        if ($note < 1 || $note > 5) {
            throw new MetierException('La note doit être comprise entre 1 et 5.');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    public function getUtilisateurId(): int
    {
        return $this->utilisateurId;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function getStatut(): StatutAvis
    {
        return $this->statut;
    }

    public function getCreeLe(): ?DateTimeImmutable
    {
        return $this->creeLe;
    }

    /** Nom affiché publiquement : prénom + initiale du nom (discrétion, RGPD). */
    public function getAuteurAffiche(): string
    {
        $initiale = $this->auteurNom ? ' ' . mb_strtoupper(mb_substr($this->auteurNom, 0, 1)) . '.' : '';
        return ($this->auteurPrenom ?? 'Client') . $initiale;
    }

    public function getAuteurPrenom(): ?string
    {
        return $this->auteurPrenom;
    }

    public function getAuteurNom(): ?string
    {
        return $this->auteurNom;
    }

    public function getMenuTitre(): ?string
    {
        return $this->menuTitre;
    }
}
