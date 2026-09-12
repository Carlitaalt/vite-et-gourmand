<?php

namespace App\Security;

/**
 * Protection CSRF : chaque formulaire (et chaque appel fetch) envoie un jeton secret lié à la session.
 * Un site tiers ne peut pas le connaître, il ne peut donc pas déclencher d'action à la place de l'utilisateur.
 */
final class Csrf
{
    public static function jeton(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    /** Champ caché à placer dans chaque formulaire POST. */
    public static function champ(): string
    {
        return '<input type="hidden" name="csrf" value="' . self::jeton() . '">';
    }

    /** Vérifie le jeton reçu (champ de formulaire ou en-tête X-CSRF-Token pour fetch). */
    public static function estValide(): bool
    {
        $jeton = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        return is_string($jeton) && hash_equals(self::jeton(), $jeton);
    }
}
