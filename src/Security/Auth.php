<?php

namespace App\Security;

use App\Entity\Utilisateur;
use App\Enum\Role;

/**
 * Gestion de la session utilisateur et contrôle d'accès par rôle.
 */
final class Auth
{
    public static function connecter(Utilisateur $utilisateur): void
    {
        // Nouvel identifiant de session à la connexion : protection contre la fixation de session
        session_regenerate_id(true);

        $_SESSION['user_id'] = $utilisateur->getId();
        $_SESSION['user_prenom'] = $utilisateur->getPrenom();
        $_SESSION['user_role'] = $utilisateur->getRole()->value;
    }

    public static function deconnecter(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public static function estConnecte(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function prenom(): ?string
    {
        return $_SESSION['user_prenom'] ?? null;
    }

    public static function mettreAJourPrenom(string $prenom): void
    {
        $_SESSION['user_prenom'] = $prenom;
    }

    public static function role(): ?Role
    {
        return isset($_SESSION['user_role']) ? Role::tryFrom((int) $_SESSION['user_role']) : null;
    }

    /** L'administrateur a aussi tous les droits de l'employé (hiérarchie des rôles). */
    public static function aRole(Role $roleRequis): bool
    {
        $role = self::role();

        return $role !== null && $role->aAuMoins($roleRequis);
    }

    public static function exigerConnexion(): void
    {
        if (!self::estConnecte()) {
            $_SESSION['redirect_apres_connexion'] = $_SERVER['REQUEST_URI'] ?? null;
            header('Location: ../pages/connexion.php');
            exit;
        }
    }

    public static function exigerRole(Role $roleRequis): void
    {
        self::exigerConnexion();

        if (!self::aRole($roleRequis)) {
            header('Location: ../pages/accueil.php');
            exit;
        }
    }
}
