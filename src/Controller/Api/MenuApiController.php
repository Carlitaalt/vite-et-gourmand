<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Service\MenuService;

/**
 * GET api/menus.php?prix_max=&prix_min=&theme=&regime=&personnes=
 * Appelé en fetch par la page Menus pour actualiser la liste sans recharger la page.
 */
class MenuApiController extends AbstractController
{
    public function __construct(private MenuService $menus = new MenuService())
    {
    }

    public function rechercher(): never
    {
        $menus = $this->menus->rechercher($_GET);

        $this->json([
            'nombre' => count($menus),
            'menus' => $menus,
        ]);
    }
}
