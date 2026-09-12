<?php

namespace App\Controller;

use App\Core\Flash;
use App\Entity\Commande;
use App\Enum\StatutCommande;
use App\Security\Auth;
use App\Service\AvisService;
use App\Service\CommandeService;
use App\Service\UtilisateurService;

/**
 * Espace utilisateur : commandes (suivi, modification, annulation), avis et profil.
 */
class CompteController extends AbstractController
{
    private const PAGE = 'mon-compte.php';

    public function __construct(
        private UtilisateurService $utilisateurs = new UtilisateurService(),
        private CommandeService $commandes = new CommandeService(),
        private AvisService $avis = new AvisService(),
    ) {
    }

    public function index(): void
    {
        Auth::exigerConnexion();
        $id = Auth::id();

        if ($this->estPost()) {
            $this->verifierCsrf(self::PAGE);
            $this->traiterAction($id, $_POST['action'] ?? '', (int) ($_POST['commande_id'] ?? 0));
        }

        $commandes = $this->commandes->commandesDuClient($id);

        $this->render('mon-compte', [
            'pageTitle' => 'Mon compte',
            'currentPage' => 'mon-compte',
            'utilisateur' => $this->utilisateurs->trouver($id),
            'commandesEnCours' => array_filter($commandes, fn(Commande $c) => !$c->getStatut()->estFinal()),
            'commandesHistorique' => array_filter($commandes, fn(Commande $c) => $c->getStatut()->estFinal()),
            'commandesTerminees' => array_filter($commandes, fn(Commande $c) => $c->getStatut() === StatutCommande::Terminee),
            'scripts' => ['mon-compte.js'],
        ]);
    }

    private function traiterAction(int $id, string $action, int $commandeId): never
    {
        match ($action) {
            'update_infos' => $this->executer(
                function () use ($id): void {
                    $utilisateur = $this->utilisateurs->modifierProfil($id, $_POST);
                    Auth::mettreAJourPrenom($utilisateur->getPrenom());
                },
                'Vos informations ont été mises à jour.',
                self::PAGE . '#infos'
            ),
            'modifier_commande' => $this->executer(
                fn() => $this->commandes->modifierParClient($id, $commandeId, $_POST),
                'Votre commande a été modifiée et son prix recalculé.',
                self::PAGE
            ),
            'annuler_commande' => $this->executer(
                fn() => $this->commandes->annulerParClient($id, $commandeId),
                'Votre commande a été annulée.',
                self::PAGE
            ),
            'donner_avis' => $this->executer(
                fn() => $this->avis->deposer($id, $commandeId, (int) ($_POST['note'] ?? 0), $_POST['commentaire'] ?? ''),
                'Merci ! Votre avis sera visible après validation par notre équipe.',
                self::PAGE . '#avis'
            ),
            'supprimer_compte' => $this->supprimerCompte($id),
            default => $this->rediriger(self::PAGE),
        };
    }

    private function supprimerCompte(int $id): never
    {
        $this->utilisateurs->supprimerCompte($id);
        Auth::deconnecter();

        session_start();
        Flash::succes('Votre compte a bien été supprimé.');
        $this->rediriger('accueil.php');
    }
}
