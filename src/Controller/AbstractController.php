<?php

namespace App\Controller;

use App\Core\Flash;
use App\Core\View;
use App\Exception\MetierException;
use App\Security\Csrf;

/**
 * Classe mère des contrôleurs. Un contrôleur reste léger : il lit la requête,
 * délègue le travail à un service, puis choisit la réponse (page HTML, redirection ou JSON).
 */
abstract class AbstractController
{
    protected function render(string $template, array $donnees = []): void
    {
        View::render($template, $donnees);
    }

    protected function rediriger(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function estPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function verifierCsrf(string $urlRetour): void
    {
        if (!Csrf::estValide()) {
            Flash::erreur('Votre session a expiré. Merci de renvoyer le formulaire.');
            $this->rediriger($urlRetour);
        }
    }

    /**
     * Exécute une action métier, mémorise le message de succès ou l'erreur, puis redirige
     * (pattern Post/Redirect/Get : recharger la page ne renvoie pas le formulaire).
     */
    protected function executer(callable $action, string $messageSucces, string $urlRetour): never
    {
        try {
            $action();
            Flash::succes($messageSucces);
        } catch (MetierException $e) {
            Flash::erreur($e->getMessage());
        }

        $this->rediriger($urlRetour);
    }

    /** Réponse JSON pour les appels fetch. */
    protected function json(mixed $donnees, int $codeHttp = 200): never
    {
        http_response_code($codeHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        exit;
    }
}
