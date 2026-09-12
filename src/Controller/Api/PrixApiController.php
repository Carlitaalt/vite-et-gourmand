<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Exception\MetierException;
use App\Service\CommandeService;

/**
 * GET api/prix-commande.php?menu_id=&nb_personnes=&ville=&distance_km=
 * Appelé en fetch par la page Commande : le prix est calculé par le serveur, avec exactement
 * les mêmes règles que lors de l'enregistrement (aucune règle de prix dupliquée en JavaScript).
 */
class PrixApiController extends AbstractController
{
    public function __construct(private CommandeService $commandes = new CommandeService())
    {
    }

    public function calculer(): never
    {
        try {
            $prix = $this->commandes->simulerPrix(
                (int) ($_GET['menu_id'] ?? 0),
                (int) ($_GET['nb_personnes'] ?? 0),
                (string) ($_GET['ville'] ?? ''),
                (float) ($_GET['distance_km'] ?? 0)
            );
            $this->json($prix);
        } catch (MetierException $e) {
            $this->json(['erreur' => $e->getMessage()], 422);
        }
    }
}
