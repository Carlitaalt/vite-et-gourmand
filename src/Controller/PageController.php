<?php

namespace App\Controller;

/** Pages de contenu fixe (mentions légales, CGV). */
class PageController extends AbstractController
{
    public function afficher(string $template, string $titre): void
    {
        $this->render($template, [
            'pageTitle' => $titre,
            'currentPage' => $template,
        ]);
    }
}
