<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Enum\Role;
use App\Exception\MetierException;
use App\Security\Auth;
use App\Security\Csrf;
use App\Service\AvisService;

/**
 * POST api/avis.php  (JSON : {"avis_id": 3, "decision": "publier"|"refuser"}, en-tête X-CSRF-Token)
 * Appelé en fetch depuis l'espace employé / administrateur pour modérer un avis sans recharger la page.
 */
class AvisApiController extends AbstractController
{
    public function __construct(private AvisService $avis = new AvisService())
    {
    }

    public function moderer(): never
    {
        if (!Auth::aRole(Role::Employe)) {
            $this->json(['erreur' => 'Accès réservé au personnel.'], 403);
        }
        if (!$this->estPost() || !Csrf::estValide()) {
            $this->json(['erreur' => 'Requête invalide. Rechargez la page.'], 400);
        }

        $donnees = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $statut = $this->avis->moderer((int) ($donnees['avis_id'] ?? 0), ($donnees['decision'] ?? '') === 'publier');

            $this->json([
                'statut' => $statut->value,
                'libelle' => $statut->libelle(),
                'classe' => $statut->classeCss(),
            ]);
        } catch (MetierException $e) {
            $this->json(['erreur' => $e->getMessage()], 422);
        }
    }
}
