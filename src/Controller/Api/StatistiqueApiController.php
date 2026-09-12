<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Enum\Role;
use App\Security\Auth;
use App\Service\StatistiqueService;

/**
 * GET api/statistiques.php?menu=&debut=AAAA-MM-JJ&fin=AAAA-MM-JJ
 * Appelé en fetch par l'onglet Statistiques de l'administrateur à chaque changement de filtre.
 */
class StatistiqueApiController extends AbstractController
{
    public function __construct(private StatistiqueService $statistiques = new StatistiqueService())
    {
    }

    public function index(): never
    {
        if (!Auth::aRole(Role::Administrateur)) {
            $this->json(['erreur' => 'Accès réservé à l\'administrateur.'], 403);
        }

        $debut = $_GET['debut'] ?? null;
        $fin = $_GET['fin'] ?? null;

        $this->json([
            'commandesParMenu' => $this->statistiques->commandesParMenu($debut, $fin),
            'chiffreAffaires' => $this->statistiques->chiffreAffaires($_GET['menu'] ?? null, $debut, $fin),
        ]);
    }
}
