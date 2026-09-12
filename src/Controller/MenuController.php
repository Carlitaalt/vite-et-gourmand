<?php

namespace App\Controller;

use App\Security\Auth;
use App\Service\MenuService;
use App\Service\ReferenceService;

class MenuController extends AbstractController
{
    public function __construct(
        private MenuService $menus = new MenuService(),
        private ReferenceService $references = new ReferenceService(),
    ) {
    }

    /**
     * Vue globale : la liste est rendue par le serveur (filtres en paramètres GET si JavaScript est désactivé),
     * puis actualisée par fetch à chaque modification d'un filtre.
     */
    public function liste(): void
    {
        $this->render('menus', [
            'pageTitle' => 'Nos menus',
            'currentPage' => 'menus',
            'menus' => $this->menus->rechercher($_GET),
            'filtres' => $_GET,
            'themes' => $this->references->themes(),
            'regimes' => $this->references->regimes(),
            'scripts' => ['menus.js'],
        ]);
    }

    public function detail(): void
    {
        $menu = $this->menus->detailPublic((int) ($_GET['id'] ?? 0));

        if ($menu === null) {
            http_response_code(404);
            $this->render('erreur', [
                'pageTitle' => 'Menu introuvable',
                'message' => "Ce menu n'existe pas ou n'est plus proposé.",
            ]);
            return;
        }

        $this->render('menu-details', [
            'pageTitle' => $menu->getTitre(),
            'currentPage' => 'menus',
            'menu' => $menu,
            'estConnecte' => Auth::estConnecte(),
        ]);
    }
}
