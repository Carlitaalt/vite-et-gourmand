<?php

namespace App\Core;

/**
 * Messages "flash" : stockés en session, affichés une seule fois après une redirection
 * (pattern Post/Redirect/Get : un rechargement de page ne renvoie jamais le formulaire).
 */
final class Flash
{
    public static function succes(string $message): void
    {
        $_SESSION['flash']['succes'][] = $message;
    }

    public static function erreur(string $message): void
    {
        $_SESSION['flash']['erreur'][] = $message;
    }

    /**
     * @return array{succes: string[], erreur: string[]}
     */
    public static function recuperer(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return [
            'succes' => $messages['succes'] ?? [],
            'erreur' => $messages['erreur'] ?? [],
        ];
    }

    /** Conserve la saisie d'un formulaire pour le pré-remplir après une erreur. */
    public static function conserverSaisie(array $saisie): void
    {
        $_SESSION['flash_saisie'] = $saisie;
    }

    public static function saisie(): array
    {
        $saisie = $_SESSION['flash_saisie'] ?? [];
        unset($_SESSION['flash_saisie']);
        return $saisie;
    }
}
