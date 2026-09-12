<?php

/**
 * Point d'entrée commun à toutes les pages : autoload Composer, configuration, session.
 * Chaque fichier de pages/, actions/ et api/ commence par require_once de ce fichier.
 */

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Paris');

// Session sécurisée : cookie inaccessible au JavaScript, envoyé uniquement en HTTPS en production,
// et refus des identifiants de session inventés par le client (protection contre la fixation de session)
if (session_status() === PHP_SESSION_NONE) {
    $estHttps = !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => $estHttps,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

// Toute exception non prévue est journalisée côté serveur, l'utilisateur ne voit qu'un message générique
set_exception_handler(function (Throwable $e): void {
    error_log('[ERREUR] ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
    http_response_code(500);
    echo 'Une erreur technique est survenue. Veuillez réessayer plus tard.';
});
