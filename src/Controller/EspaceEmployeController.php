<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Enum\Role;
use App\Enum\StatutAvis;
use App\Enum\StatutCommande;
use App\Exception\MetierException;
use App\Security\Auth;
use App\Service\AvisService;
use App\Service\CommandeService;
use App\Service\EmployeService;
use App\Service\HoraireService;
use App\Service\MenuService;
use App\Service\PlatService;
use App\Service\ReferenceService;
use App\Service\UtilisateurService;

/**
 * Espace employé : commandes, avis, menus, plats et horaires.
 * L'espace administrateur hérite de ce contrôleur (voir EspaceAdminController) : les actions communes
 * ne sont écrites qu'une seule fois.
 */
class EspaceEmployeController extends AbstractController
{
    protected const PAGE = 'espace-employe.php';
    protected const TEMPLATE = 'espace-employe';
    protected const ROLE_REQUIS = Role::Employe;

    public function __construct(
        protected CommandeService $commandes = new CommandeService(),
        protected MenuService $menus = new MenuService(),
        protected PlatService $plats = new PlatService(),
        protected AvisService $avis = new AvisService(),
        protected HoraireService $horaires = new HoraireService(),
        protected ReferenceService $references = new ReferenceService(),
        protected UtilisateurService $utilisateurs = new UtilisateurService(),
        protected EmployeService $employes = new EmployeService(),
    ) {
    }

    public function index(): void
    {
        Auth::exigerRole(static::ROLE_REQUIS);

        if ($this->estPost()) {
            $this->verifierCsrf(static::PAGE);
            $this->traiterAction($_POST['action'] ?? '');
        }

        $this->render(static::TEMPLATE, $this->donneesPage());
    }

    /** Actions communes à l'employé et à l'administrateur. */
    protected function traiterAction(string $action): never
    {
        $entier = fn(string $cle): int => (int) ($_POST[$cle] ?? 0);

        match ($action) {
            'update_statut' => $this->executer(
                fn() => $this->commandes->changerStatut($entier('commande_id'), $entier('nouveau_statut')),
                'Le statut de la commande a été mis à jour.',
                static::PAGE . '#commandes'
            ),
            'annuler_commande' => $this->executer(
                fn() => $this->commandes->annulerParPersonnel($entier('commande_id'), $_POST['mode_contact'] ?? '', $_POST['motif_annulation'] ?? ''),
                'La commande a été annulée et le client prévenu par e-mail.',
                static::PAGE . '#commandes'
            ),
            'valider_avis', 'refuser_avis' => $this->executer(
                fn() => $this->avis->moderer($entier('avis_id'), $action === 'valider_avis'),
                'L\'avis a été traité.',
                static::PAGE . '#avis-employe'
            ),
            'update_menu' => $this->executer(
                fn() => $this->menus->enregistrer($_POST, $_FILES['menu_photo'] ?? []),
                'Le menu a été enregistré.',
                static::PAGE . '#menus'
            ),
            'delete_menu' => $this->executer(
                fn() => $this->menus->supprimer($entier('menu_id')),
                'Le menu a été supprimé.',
                static::PAGE . '#menus'
            ),
            'delete_menu_image' => $this->executer(
                fn() => $this->menus->supprimerImage($entier('image_id')),
                'La photo a été supprimée.',
                static::PAGE . '#menus'
            ),
            'update_plat' => $this->executer(
                fn() => $this->plats->enregistrer($_POST),
                'Le plat a été enregistré.',
                static::PAGE . '#menus'
            ),
            'delete_plat' => $this->executer(
                fn() => $this->plats->supprimer($entier('plat_id')),
                'Le plat a été supprimé.',
                static::PAGE . '#menus'
            ),
            'update_horaires' => $this->executer(
                fn() => $this->horaires->mettreAJour($_POST['ouvert'] ?? [], $_POST['debut'] ?? [], $_POST['fin'] ?? []),
                'Les horaires ont été mis à jour.',
                static::PAGE . '#horaires'
            ),
            default => $this->executer(fn() => throw new MetierException('Action inconnue.'), '', static::PAGE),
        };
    }

    protected function donneesPage(): array
    {
        $commandes = $this->commandes->toutesLesCommandes();
        $avis = $this->avis->tousLesAvis();
        $employe = $this->employes->trouver(Auth::id());

        return [
            'pageTitle' => 'Espace employé',
            'currentPage' => 'espace-employe',
            'profil' => $this->utilisateurs->trouver(Auth::id()),
            'badgeProfil' => $employe?->getPoste() ?? Auth::role()?->libelle(),
            'commandes' => $commandes,
            'avis' => $avis,
            'menus' => $this->menus->tousLesMenus(),
            'plats' => $this->plats->tousLesPlats(),
            'horaires' => $this->horaires->tousLesHoraires(),
            'themes' => $this->references->themes(),
            'regimes' => $this->references->regimes(),
            'allergenes' => $this->references->allergenes(),
            'modesContact' => CommandeService::MODES_CONTACT,
            'nbEnAttente' => count(array_filter($commandes, fn(Commande $c) => $c->getStatut() === StatutCommande::EnAttente)),
            'nbEnCours' => count(array_filter($commandes, fn(Commande $c) => $c->getStatut()->estEnCours())),
            'nbAvisAttente' => count(array_filter($avis, fn(Avis $a) => $a->getStatut() === StatutAvis::EnAttente)),
            'scripts' => ['gestion.js'],
        ];
    }
}
