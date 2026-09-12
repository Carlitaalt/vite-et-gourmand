<?php

namespace App\Controller;

use App\Core\Flash;
use App\Enum\Role;
use App\Exception\MetierException;
use App\Security\Auth;
use App\Service\AuthService;

/**
 * Connexion, inscription, déconnexion et mot de passe oublié.
 * Les formulaires de connexion et d'inscription sont envoyés à actions/connexion.php et actions/inscription.php.
 */
class AuthController extends AbstractController
{
    public function __construct(private AuthService $auth = new AuthService())
    {
    }

    public function pageConnexion(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger('accueil.php');
        }

        $this->render('connexion', [
            'pageTitle' => 'Connexion',
            'currentPage' => 'connexion',
            'email' => Flash::saisie()['email'] ?? '',
        ]);
    }

    public function connexion(): never
    {
        if (!$this->estPost()) {
            $this->rediriger('../pages/connexion.php');
        }
        $this->verifierCsrf('../pages/connexion.php');

        try {
            $utilisateur = $this->auth->authentifier($_POST['email'] ?? '', $_POST['mot_de_passe'] ?? '');
        } catch (MetierException $e) {
            Flash::erreur($e->getMessage());
            Flash::conserverSaisie(['email' => $_POST['email'] ?? '']);
            $this->rediriger('../pages/connexion.php');
        }

        Auth::connecter($utilisateur);

        // Retour à la page demandée avant connexion (uniquement un chemin interne au site : pas de redirection ouverte)
        $destination = $_SESSION['redirect_apres_connexion'] ?? null;
        unset($_SESSION['redirect_apres_connexion']);

        if (!is_string($destination) || !str_starts_with($destination, '/') || str_starts_with($destination, '//')) {
            $destination = match ($utilisateur->getRole()) {
                Role::Administrateur => '../pages/espace-admin.php',
                Role::Employe => '../pages/espace-employe.php',
                Role::Utilisateur => '../pages/accueil.php',
            };
        }

        $this->rediriger($destination);
    }

    public function pageInscription(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger('accueil.php');
        }

        $this->render('inscription', [
            'pageTitle' => 'Inscription',
            'currentPage' => 'inscription',
            'saisie' => Flash::saisie(),
            'scripts' => ['mot-de-passe.js'],
        ]);
    }

    public function inscription(): never
    {
        if (!$this->estPost()) {
            $this->rediriger('../pages/inscription.php');
        }
        $this->verifierCsrf('../pages/inscription.php');

        try {
            $this->auth->inscrire($_POST);
            Flash::succes('Compte créé avec succès ! Un e-mail de bienvenue vous a été envoyé. Vous pouvez vous connecter.');
            $this->rediriger('../pages/connexion.php');
        } catch (MetierException $e) {
            Flash::erreur($e->getMessage());
            // On ne conserve jamais les mots de passe saisis
            Flash::conserverSaisie(array_diff_key($_POST, array_flip(['mot_de_passe', 'mot_de_passe_conf', 'csrf'])));
            $this->rediriger('../pages/inscription.php');
        }
    }

    public function deconnexion(): never
    {
        Auth::deconnecter();
        $this->rediriger('../pages/accueil.php');
    }

    public function motDePasseOublie(): void
    {
        if ($this->estPost()) {
            $this->verifierCsrf('mot-de-passe-oublie.php');
            $this->executer(
                fn() => $this->auth->demanderReinitialisation($_POST['email'] ?? ''),
                'Si cette adresse est associée à un compte, vous allez recevoir un e-mail avec un lien de réinitialisation.',
                'mot-de-passe-oublie.php'
            );
        }

        $this->render('mot-de-passe-oublie', [
            'pageTitle' => 'Mot de passe oublié',
            'currentPage' => 'connexion',
        ]);
    }

    public function reinitialiser(): void
    {
        $jeton = (string) ($_GET['token'] ?? '');
        $urlPage = 'reintialiser.php?token=' . urlencode($jeton);

        if ($this->estPost()) {
            $this->verifierCsrf($urlPage);

            try {
                $this->auth->reinitialiserMotDePasse($jeton, $_POST['mot_de_passe'] ?? '', $_POST['mot_de_passe_conf'] ?? '');
                Flash::succes('Votre mot de passe a été mis à jour. Vous pouvez vous connecter.');
                $this->rediriger('connexion.php');
            } catch (MetierException $e) {
                Flash::erreur($e->getMessage());
                $this->rediriger($urlPage);
            }
        }

        $this->render('reinitialiser', [
            'pageTitle' => 'Nouveau mot de passe',
            'currentPage' => 'connexion',
            'jeton' => $jeton,
            'jetonValide' => $this->auth->jetonEstValide($jeton),
            'scripts' => ['mot-de-passe.js'],
        ]);
    }
}
