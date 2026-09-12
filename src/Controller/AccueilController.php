<?php

namespace App\Controller;

use App\Service\AvisService;

class AccueilController extends AbstractController
{
    public function __construct(private AvisService $avis = new AvisService())
    {
    }

    public function index(): void
    {
        $this->render('accueil', [
            'pageTitle' => 'Accueil',
            'currentPage' => 'accueil',
            'avis' => $this->avis->avisPublies(6),
        ]);
    }
}
