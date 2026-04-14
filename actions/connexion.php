<?php

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../pages/connexion.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$mdp = trim($_POST['mot_de_passe'] ?? '');
$erreur = '';

//Validation basique

if(empty($email) || empty($mdp)) {
    $erreur = 'Veuillez remplir tous les champs.';
}

//Vérification en base de donnée
if(empty($erreur)) {
    try {
        $stmt = $pdo->prepare("
        SELECT u.utilisateur_id, u.prenom, u.nom, u.email, u.mot_de_passe, u.actif, r.libelle AS role
        FROM utilisateur u
        JOIN role r ON r.role_id = u.role_id
        WHERE u.email = :email
        LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if(!$user) {
            $erreur = 'Email ou mot de passe incorrect.';

        } elseif (!$user['actif']) {
            $erreur = 'Ce compte a été désactivé. Contactez l\'administrateur.';

        } elseif (!password_verify($mdp, $user['mot_de_passe'])) {
            $erreur = 'Email ou mot de passe incorrect.';

        } else {
            connecter_utilisateur($user);

            //Rediriger vers la page demandée avant connexion, sinon l'accueil
            $redirect = $_SESSION['redirect_apres_connexion'] ?? null;
            unset($_SESSION['redirect_apres_connexion']);

            //Redirection selon le rôle
            if($redirect) {
                header('Location: ' . $redirect);

            } elseif ($user['role'] === 'admin') {
                header('Location: ../pages/espace-admin.php');

            } elseif ($user['role'] === 'employe') {
                header('Location: ../pages/espace-employe.php');

            } else {
                header('Location: ../pages/accueil.php');

            }
            exit;

        }

        } catch (PDOException $e) {
            error_log('[CONNEXION]' . $e->getMessage());
            $erreur = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }

    $_SESSION['erreur_connexion'] = $erreur;
    $_SESSION['email_saisi'] = htmlspecialchars($email);
    header('Location: ../pages/connexion.php');
    exit;
