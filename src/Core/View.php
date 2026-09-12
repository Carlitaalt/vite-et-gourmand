<?php

namespace App\Core;

use App\Repository\HoraireRepository;

/**
 * Affichage des templates (dossier templates/) : la vue ne contient que du HTML,
 * les données lui sont transmises déjà prêtes par le contrôleur.
 */
final class View
{
    private const DOSSIER = __DIR__ . '/../../templates/';

    /**
     * Affiche une page complète : en-tête + navigation, template, pied de page.
     */
    public static function render(string $template, array $donnees = []): void
    {
        $donnees['rootPath'] ??= '../';
        $donnees['flashs'] = Flash::recuperer();
        // Le pied de page affiche les horaires sur toutes les pages
        $donnees['horairesFooter'] = (new HoraireRepository())->findAll();

        extract($donnees, EXTR_SKIP);

        require self::DOSSIER . 'layout/header.php';
        require self::DOSSIER . $template . '.php';
        require self::DOSSIER . 'layout/footer.php';
    }

    /**
     * Affiche un morceau de template réutilisable (ex : un onglet partagé entre admin et employé).
     */
    public static function partial(string $template, array $donnees = []): void
    {
        extract($donnees, EXTR_SKIP);
        require self::DOSSIER . $template . '.php';
    }
}
