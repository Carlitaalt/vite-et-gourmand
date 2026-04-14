<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

require_once '../includes/db.php';
require_once '../includes/auth.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/inscription.php');
    exit;
}

$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$mdp = $_POST['mot_de_passe'] ?? '';
$mdp_conf = $_POST['mot_de_passe_conf'] ?? '';

$erreur = '';

//Validation des champs

if(empty($prenom) || empty($nom) || empty($email) || empty($mdp)) {
    $erreur = 'Veuillez remplir tous les champs obligatoires.';

} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreur = 'Adresse e-mail invalide.';

} elseif($mdp !== $mdp_conf) {
    $erreur = 'Les mots de passe ne correspondent pas.';

} else {
    //Validation mot de passe : 10 caractères min, 1maj, 1min, 1 chiffre, 1 spécial
    $regexMdp = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/';

    if(!preg_match($regexMdp, $mdp)) {
        $erreur = 'Le mot de passe doit contenir au moins 10 caractères, ' . 'une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }
}

//Vérification email déjà utilisé

if(empty($erreur)) {
    try {
        $check = $pdo->prepare("SELECT utilisateur_id
                                FROM utilisateur
                                WHERE email = :email
                                ");
        $check->execute([':email' => $email]);
        if($check->fetch()) {
            $erreur = 'Cette adresse e-mail est déjà utilisée.';
        }
    } catch (PDOException $e) {
        error_log('[INSCRIPTION] ' . $e->getMessage());
        $erreur = 'Une erreur est survenue. Veuillez réessayer.';
    }
}

//Insertion en base
if(empty($erreur)) {
    try {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
                                INSERT INTO utilisateur (role_id, prenom, nom, email, mot_de_passe, telephone, adresse_postale)
                                VALUES (1, :prenom, :nom, :email, :mdp, :telephone, :adresse)
                                ");
        $stmt->execute([
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':mdp' => $hash,
            ':telephone' => $telephone ?: null,
            ':adresse' => $adresse ?: null,
        ]);

        //Mail de bienvenue
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Port = 2525;
            $mail->Username = 'a42fbdd3effc59';
            $mail->Password = '6bbe288f32e485';
            $mail->CharSet = 'UTF-8';

            //Destinataires
            $mail->setFrom('bienvenue@vite-gourmand.fr', 'Vite & Gourmand');
            $mail->addAddress($email, $prenom . ' ' . $nom);

            //Contenu du message
            $mail->isHTML(true);
            $mail->Subject = 'Bienvenue chez Vite & Gourmand !';
            $mail->Body = "
            <div style='font-family: sans-serif; color: #333;'>
                <h1 style='color: #27ae60;'>Bienvenue $prenom !</h1>
                <p>Ton compte a été créé avec succès sur <strong>Vite & Gourmand</strong>.</p>
                <p>Tu peux maintenant te connecter pour passer ta première commande.</p>
                <br>
                <p>À très bientôt !</p>
            </div>
            ";

            $mail->send();
        } catch(Exception $e) {
            error_log("Erreur PHPMailer : " . $mail->ErrorInfo);
        }

        $_SESSION['succes_inscription'] = 'Compte créé avec succès ! Vous pouvez vous connecter.';
        header('Location: ../pages/connexion.php');
        exit;
    } catch (PDOException $e) {
        error_log('[INSCRIPTION] ' . $e->getMessage());
        $erreur = 'Une erreur est survenue. Veuillez réessayer.';
    }
}

//Echec
$_SESSION['erreur_inscription'] = $erreur;

//Renvoyer les champs pour re-remplir le formulaire
$_SESSION['form_inscription'] = compact('prenom', 'nom', 'email', 'telephone', 'adresse');
header('Location: ../pages/inscription.php');
exit;
