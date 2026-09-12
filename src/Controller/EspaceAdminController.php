<?php

namespace App\Controller;

use App\Enum\Role;
use App\Service\StatistiqueService;

/**
 * Espace administrateur : tout ce que fait l'employé (héritage), plus la gestion des comptes employés
 * et les statistiques (nombre de commandes par menu depuis MongoDB, chiffre d'affaires).
 */
class EspaceAdminController extends EspaceEmployeController
{
    protected const PAGE = 'espace-admin.php';
    protected const TEMPLATE = 'espace-admin';
    protected const ROLE_REQUIS = Role::Administrateur;

    protected function traiterAction(string $action): never
    {
        $employeId = (int) ($_POST['employe_id'] ?? 0);

        match ($action) {
            'creer_employe' => $this->executer(
                fn() => $this->employes->creer($_POST),
                'Le compte employé a été créé. L\'employé a été prévenu par e-mail (sans son mot de passe).',
                static::PAGE . '#employes'
            ),
            'toggle_employe' => $this->executer(
                fn() => $this->employes->changerActivation($employeId, ($_POST['actif'] ?? '') === '1'),
                'Le statut du compte employé a été mis à jour.',
                static::PAGE . '#employes'
            ),
            'modifier_employe' => $this->executer(
                fn() => $this->employes->modifierContrat($employeId, $_POST['poste'] ?? '', $_POST['salaire'] ?? null),
                'La fiche employé a été mise à jour.',
                static::PAGE . '#employes'
            ),
            // Toutes les autres actions sont celles de l'employé
            default => parent::traiterAction($action),
        };
    }

    protected function donneesPage(): array
    {
        $statistiques = new StatistiqueService();

        return [
            ...parent::donneesPage(),
            'pageTitle' => 'Espace administrateur',
            'currentPage' => 'espace-admin',
            'employes' => $this->employes->tousLesEmployes(),
            'commandesParMenu' => $statistiques->commandesParMenu(),
            'chiffreAffaires' => $statistiques->chiffreAffaires(),
            'scripts' => [
                'gestion.js',
                'mot-de-passe.js',
                'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js',
                'statistiques.js',
            ],
        ];
    }
}
