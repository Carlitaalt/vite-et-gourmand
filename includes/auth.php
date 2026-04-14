<?php
// Gestion des sessions et protection des pages par rôle

function connecter_utilisateur(array $user): void {
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['utilisateur_id'];
    $_SESSION['user_prenom'] = $user['prenom'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

function deconnecter(): void {
    $_SESSION = [];

    if(ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }

    session_destroy();
}

function est_connecte(): bool {
    return isset($_SESSION['user_id']);
}

function get_role(): ?string {
    return $_SESSION['user_role'] ?? null;
}

function a_role(string $role_requis): bool {
    $hierarchie = [
        'utilisateur' => 1,
        'employe' => 2,
        'administrateur' => 3,
    ];

    $role_actuel = get_role();
    if($role_actuel === null) return false;

    $niveau_actuel = $hierarchie[$role_actuel] ?? 0;
    $niveau_requis = $hierarchie[$role_requis] ?? 99;

    return $niveau_actuel >= $niveau_requis;
}

//Fonctions de protection

function exiger_connexion(string $racine = '../'): void {
    if(!est_connecte()) {
        $_SESSION['redirect_apres_connexion'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $racine . 'pages/connexion.php');
        exit;
    }
}

function exiger_role(string $role_requis, string $racine = '../'): void {
    exiger_connexion($racine);

    if(!a_role($role_requis)) {
        header('Location: ' . $racine . 'pages/accueil.php');
        exit;
    }
}

function get_user(): ?array {
    if(!est_connecte()) return null;

    return ['id' => $_SESSION['user_id'],
            'prenom' => $_SESSION['user_prenom'],
            'nom' => $_SESSION['user_nom'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            ];
}

