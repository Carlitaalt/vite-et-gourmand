<?php

namespace App\Service;

use App\Core\Database;
use App\Entity\Commande;
use App\Entity\DetailPrix;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use App\Enum\StatutCommande;
use App\Exception\MetierException;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Repository\StatistiqueRepository;
use DateTimeImmutable;

/**
 * Règles de gestion des commandes : calcul du prix, passage, modification, suivi et annulation.
 */
class CommandeService
{
    public const VILLE_SIEGE = 'bordeaux';
    public const FRAIS_LIVRAISON_BASE = 5.00;
    public const FRAIS_PAR_KM = 0.59;
    public const TAUX_REMISE = 0.10;
    public const PERSONNES_EN_PLUS_POUR_REMISE = 5;

    /** Modes de contact acceptés (valeurs de la colonne ENUM `mode_contact`). */
    public const MODES_CONTACT = ['telephone' => 'Appel GSM', 'email' => 'E-mail'];

    public function __construct(
        private CommandeRepository $commandes = new CommandeRepository(),
        private MenuRepository $menus = new MenuRepository(),
        private StatistiqueRepository $statistiques = new StatistiqueRepository(),
        private NotificationService $notifications = new NotificationService(),
    ) {
    }

    // --- Calcul du prix ---

    /**
     * - minimum de personnes imposé par le menu ;
     * - 10 % de remise à partir de 5 personnes de plus que ce minimum ;
     * - livraison gratuite à Bordeaux, sinon 5 € + 0,59 € par kilomètre.
     */
    public function calculerPrix(Menu $menu, int $nombrePersonnes, string $ville, float $distanceKm): DetailPrix
    {
        $minimum = $menu->getNombrePersonneMinimum();
        if ($nombrePersonnes < $minimum) {
            throw new MetierException("Ce menu se commande pour $minimum personnes minimum.");
        }

        $sousTotal = round($menu->getPrixParPersonne() * $nombrePersonnes, 2);
        $remise = $nombrePersonnes >= $minimum + self::PERSONNES_EN_PLUS_POUR_REMISE
            ? round($sousTotal * self::TAUX_REMISE, 2)
            : 0.0;

        return new DetailPrix(
            $menu->getPrixParPersonne(),
            $nombrePersonnes,
            $sousTotal,
            $remise,
            $this->calculerFraisLivraison($ville, $distanceKm),
        );
    }

    public function calculerFraisLivraison(string $ville, float $distanceKm): float
    {
        if ($this->estLivreASiege($ville)) {
            return 0.0;
        }

        return round(self::FRAIS_LIVRAISON_BASE + self::FRAIS_PAR_KM * max(0, $distanceKm), 2);
    }

    /** Utilisé par l'API (appel fetch) pour afficher le prix en direct pendant la saisie. */
    public function simulerPrix(int $menuId, int $nombrePersonnes, string $ville, float $distanceKm): DetailPrix
    {
        $menu = $this->menus->findById($menuId, true) ?? throw new MetierException('Menu introuvable.');

        return $this->calculerPrix($menu, $nombrePersonnes, $ville, $distanceKm);
    }

    // --- Côté client ---

    public function passerCommande(Utilisateur $client, array $donnees): Commande
    {
        $menu = $this->menus->findById((int) ($donnees['menu_id'] ?? 0), true);
        if ($menu === null) {
            throw new MetierException("Le menu sélectionné n'est plus disponible.");
        }
        if (!$menu->estDisponible()) {
            throw new MetierException("Ce menu n'a plus de disponibilité actuellement.");
        }

        $prestation = $this->validerPrestation($donnees);
        $prix = $this->calculerPrix($menu, (int) ($donnees['nb_personnes'] ?? 0), $prestation['ville'], $prestation['distance']);

        $commande = new Commande(
            null,
            $client->getId(),
            $menu->getId(),
            StatutCommande::EnAttente,
            $prestation['date'],
            $prestation['heure'],
            $prestation['adresse'],
            $prestation['ville'],
            $prestation['distance'],
            $prix->nombrePersonnes,
            $prix->prixParPersonne,
            $prix->fraisLivraison,
            $prix->getTotal(),
            !empty($donnees['pret_materiel']),
        );

        // Stock et commande dans la même transaction : pas de commande enregistrée sans stock disponible
        Database::transaction(function () use ($commande, $menu): void {
            if (!$this->menus->decrementerStock($menu->getId())) {
                throw new MetierException('Ce menu vient d\'être victime de son succès : il n\'est plus disponible.');
            }
            $this->commandes->inserer($commande);
        });

        $commande->setClient($client);
        $commande->setMenu($menu);

        $this->statistiques->enregistrerCommande($commande->getId(), $menu->getId(), $menu->getTitre(), $commande->getPrixTotal());
        $this->notifications->confirmationCommande($commande);

        return $commande;
    }

    /** @return Commande[] */
    public function commandesDuClient(int $clientId): array
    {
        return $this->commandes->findByUtilisateur($clientId);
    }

    /** Tout est modifiable sauf le menu, tant que la commande n'est pas acceptée. */
    public function modifierParClient(int $clientId, int $commandeId, array $donnees): Commande
    {
        $commande = $this->commandeDuClient($clientId, $commandeId);
        $menu = $this->menus->findById($commande->getMenuId()) ?? throw new MetierException('Menu introuvable.');

        $prestation = $this->validerPrestation($donnees);
        $prix = $this->calculerPrix($menu, (int) ($donnees['nombre_personnes'] ?? 0), $prestation['ville'], $prestation['distance']);

        $commande->modifierPrestation(
            $prestation['date'],
            $prestation['heure'],
            $prestation['adresse'],
            $prestation['ville'],
            $prestation['distance'],
            $prix
        );
        $this->commandes->mettreAJourPrestation($commande);

        return $commande;
    }

    public function annulerParClient(int $clientId, int $commandeId): void
    {
        $commande = $this->commandeDuClient($clientId, $commandeId);

        if (!$commande->estModifiableParClient()) {
            throw new MetierException('Cette commande a déjà été acceptée : contactez-nous pour l\'annuler.');
        }

        $commande->annuler('Annulée par le client');
        $this->enregistrerAnnulation($commande);
    }

    // --- Côté employé / administrateur ---

    /** @return Commande[] */
    public function toutesLesCommandes(): array
    {
        return $this->commandes->findAll();
    }

    public function changerStatut(int $commandeId, int $valeurStatut): Commande
    {
        $commande = $this->commandes->findById($commandeId) ?? throw new MetierException('Commande introuvable.');
        $nouveauStatut = StatutCommande::tryFrom($valeurStatut) ?? throw new MetierException('Statut inconnu.');

        $commande->passerAuStatut($nouveauStatut);
        $this->commandes->enregistrerStatut($commande);

        match ($nouveauStatut) {
            StatutCommande::RetourMateriel => $this->notifications->retourMateriel($commande),
            StatutCommande::Terminee => $this->notifications->commandeTerminee($commande),
            default => null,
        };

        return $commande;
    }

    /** L'employé doit avoir contacté le client et préciser le mode de contact et le motif. */
    public function annulerParPersonnel(int $commandeId, string $modeContact, string $motif): Commande
    {
        if (!array_key_exists($modeContact, self::MODES_CONTACT)) {
            throw new MetierException('Précisez le mode de contact utilisé avec le client (appel GSM ou e-mail).');
        }
        if (trim($motif) === '') {
            throw new MetierException('Le motif d\'annulation est obligatoire.');
        }

        $commande = $this->commandes->findById($commandeId) ?? throw new MetierException('Commande introuvable.');
        $commande->annuler(trim($motif), $modeContact);
        $this->enregistrerAnnulation($commande);
        $this->notifications->commandeAnnulee($commande);

        return $commande;
    }

    // --- Outils internes ---

    private function commandeDuClient(int $clientId, int $commandeId): Commande
    {
        $commande = $this->commandes->findById($commandeId);

        // Un client ne peut agir que sur ses propres commandes
        if ($commande === null || $commande->getUtilisateurId() !== $clientId) {
            throw new MetierException('Commande introuvable.');
        }

        return $commande;
    }

    /** Une commande annulée libère sa place dans le stock du menu. */
    private function enregistrerAnnulation(Commande $commande): void
    {
        Database::transaction(function () use ($commande): void {
            $this->commandes->enregistrerStatut($commande);
            $this->menus->incrementerStock($commande->getMenuId());
        });
    }

    private function estLivreASiege(string $ville): bool
    {
        return mb_strtolower(trim($ville)) === self::VILLE_SIEGE;
    }

    /**
     * @return array{date: DateTimeImmutable, heure: string, adresse: string, ville: string, distance: float}
     */
    private function validerPrestation(array $donnees): array
    {
        $date = trim($donnees['date_prestation'] ?? '');
        $heure = trim($donnees['heure_prestation'] ?? $donnees['heure_livraison'] ?? '');
        $adresse = trim($donnees['adresse_prestation'] ?? $donnees['adresse_livraison'] ?? '');
        $ville = trim($donnees['ville_prestation'] ?? $donnees['ville_livraison'] ?? '');

        if ($date === '' || $heure === '' || $adresse === '' || $ville === '') {
            throw new MetierException('Veuillez remplir tous les champs obligatoires.');
        }

        $datePrestation = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($datePrestation === false || $datePrestation <= new DateTimeImmutable('today')) {
            throw new MetierException('La date de prestation doit être postérieure à aujourd\'hui.');
        }
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', substr($heure, 0, 5))) {
            throw new MetierException('Heure de livraison invalide.');
        }

        $distance = $this->estLivreASiege($ville) ? 0.0 : max(0, (float) ($donnees['distance_km'] ?? 0));

        return [
            'date' => $datePrestation,
            'heure' => substr($heure, 0, 5),
            'adresse' => $adresse,
            'ville' => $ville,
            'distance' => $distance,
        ];
    }
}
