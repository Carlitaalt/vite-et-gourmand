<?php

namespace App\Controller;

use App\Core\Flash;
use App\Exception\MetierException;
use App\Security\Auth;
use App\Service\CommandeService;
use App\Service\MenuService;
use App\Service\UtilisateurService;

class CommandeController extends AbstractController
{
    public function __construct(
        private CommandeService $commandes = new CommandeService(),
        private MenuService $menus = new MenuService(),
        private UtilisateurService $utilisateurs = new UtilisateurService(),
    ) {
    }

    public function formulaire(): void
    {
        Auth::exigerConnexion();
        $client = $this->utilisateurs->trouver(Auth::id());

        if ($this->estPost()) {
            $this->verifierCsrf('commande.php');

            try {
                $commande = $this->commandes->passerCommande($client, $_POST);
                Flash::succes("Votre commande n°{$commande->getId()} a bien été enregistrée. Un e-mail de confirmation vous a été envoyé.");
                $this->rediriger('mon-compte.php');
            } catch (MetierException $e) {
                Flash::erreur($e->getMessage());
                Flash::conserverSaisie($_POST);
                $this->rediriger('commande.php?menu=' . (int) ($_POST['menu_id'] ?? 0));
            }
        }

        $this->render('commande', [
            'pageTitle' => 'Commander un menu',
            'currentPage' => 'commande',
            'client' => $client,
            'menus' => $this->menus->rechercher([]),
            'menuPreselectionne' => (int) ($_GET['menu'] ?? 0),
            'saisie' => Flash::saisie(),
            'scripts' => ['commande.js'],
        ]);
    }
}
