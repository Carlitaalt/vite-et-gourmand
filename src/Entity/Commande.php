<?php

namespace App\Entity;

use App\Enum\StatutCommande;
use App\Exception\MetierException;
use DateTimeImmutable;

/**
 * Commande d'un menu par un client (tables `commande` et `commande_menu`).
 * Porte les règles de gestion de son cycle de vie : transitions de statut, annulation, modification.
 */
class Commande
{
    /** @var EtapeSuivi[] */
    private array $historique = [];
    private ?Avis $avis = null;
    private ?Utilisateur $client = null;
    private ?Menu $menu = null;

    public function __construct(
        private ?int $id,
        private int $utilisateurId,
        private int $menuId,
        private StatutCommande $statut,
        private DateTimeImmutable $datePrestation,
        private string $heureLivraison,
        private string $adresseLivraison,
        private string $villeLivraison,
        private float $distanceKm,
        private int $nombrePersonnes,
        private float $prixUnitaire,
        private float $prixLivraison,
        private float $prixTotal,
        private bool $pretMateriel = false,
        private ?DateTimeImmutable $dateCommande = null,
        private ?string $motifAnnulation = null,
        private ?string $modeContact = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUtilisateurId(): int
    {
        return $this->utilisateurId;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function getStatut(): StatutCommande
    {
        return $this->statut;
    }

    public function getDatePrestation(): DateTimeImmutable
    {
        return $this->datePrestation;
    }

    /** Heure au format HH:MM. */
    public function getHeureLivraison(): string
    {
        return substr($this->heureLivraison, 0, 5);
    }

    public function getAdresseLivraison(): string
    {
        return $this->adresseLivraison;
    }

    public function getVilleLivraison(): string
    {
        return $this->villeLivraison;
    }

    public function getDistanceKm(): float
    {
        return $this->distanceKm;
    }

    public function getNombrePersonnes(): int
    {
        return $this->nombrePersonnes;
    }

    public function getPrixUnitaire(): float
    {
        return $this->prixUnitaire;
    }

    public function getPrixLivraison(): float
    {
        return $this->prixLivraison;
    }

    public function getPrixTotal(): float
    {
        return $this->prixTotal;
    }

    public function aPretMateriel(): bool
    {
        return $this->pretMateriel;
    }

    public function getDateCommande(): ?DateTimeImmutable
    {
        return $this->dateCommande;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function getModeContact(): ?string
    {
        return $this->modeContact;
    }

    // --- Règles de gestion ---

    /**
     * Statuts que l'équipe peut appliquer ensuite.
     * Après la livraison : "retour de matériel" si du matériel a été prêté, sinon directement "terminée".
     *
     * @return StatutCommande[]
     */
    public function getStatutsSuivants(): array
    {
        if ($this->statut === StatutCommande::Livree) {
            return [$this->pretMateriel ? StatutCommande::RetourMateriel : StatutCommande::Terminee];
        }

        return $this->statut->suivantsPossibles();
    }

    public function passerAuStatut(StatutCommande $nouveauStatut): void
    {
        if (!in_array($nouveauStatut, $this->getStatutsSuivants(), true)) {
            throw new MetierException(sprintf(
                'Une commande « %s » ne peut pas passer au statut « %s ».',
                $this->statut->libelle(),
                $nouveauStatut->libelle()
            ));
        }

        $this->statut = $nouveauStatut;
    }

    public function peutEtreAnnulee(): bool
    {
        return !$this->statut->estFinal();
    }

    public function annuler(string $motif, ?string $modeContact = null): void
    {
        if (!$this->peutEtreAnnulee()) {
            throw new MetierException('Cette commande ne peut plus être annulée.');
        }

        $this->statut = StatutCommande::Annulee;
        $this->motifAnnulation = $motif;
        $this->modeContact = $modeContact;
    }

    /** Le client peut modifier ou annuler tant qu'un employé n'a pas accepté la commande. */
    public function estModifiableParClient(): bool
    {
        return $this->statut === StatutCommande::EnAttente;
    }

    /** Tout est modifiable par le client, sauf le choix du menu. */
    public function modifierPrestation(
        DateTimeImmutable $datePrestation,
        string $heureLivraison,
        string $adresseLivraison,
        string $villeLivraison,
        float $distanceKm,
        DetailPrix $prix
    ): void {
        if (!$this->estModifiableParClient()) {
            throw new MetierException('Cette commande a déjà été acceptée : elle ne peut plus être modifiée.');
        }

        $this->datePrestation = $datePrestation;
        $this->heureLivraison = $heureLivraison;
        $this->adresseLivraison = $adresseLivraison;
        $this->villeLivraison = $villeLivraison;
        $this->distanceKm = $distanceKm;
        $this->nombrePersonnes = $prix->nombrePersonnes;
        $this->prixLivraison = $prix->fraisLivraison;
        $this->prixTotal = $prix->getTotal();
    }

    public function peutRecevoirAvis(): bool
    {
        return $this->statut === StatutCommande::Terminee && $this->avis === null;
    }

    // --- Objets associés (chargés par le repository) ---

    /** @return EtapeSuivi[] */
    public function getHistorique(): array
    {
        return $this->historique;
    }

    /** @param EtapeSuivi[] $historique */
    public function setHistorique(array $historique): void
    {
        $this->historique = $historique;
    }

    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): void
    {
        $this->avis = $avis;
    }

    public function getClient(): ?Utilisateur
    {
        return $this->client;
    }

    public function setClient(?Utilisateur $client): void
    {
        $this->client = $client;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): void
    {
        $this->menu = $menu;
    }

    public function getMenuTitre(): string
    {
        return $this->menu?->getTitre() ?? 'Menu supprimé';
    }
}
