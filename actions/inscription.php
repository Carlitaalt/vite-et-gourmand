<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ... reste du code
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
$ville = trim($_POST['ville'] ?? '');
$pays = trim($_POST['pays'] ?? '');
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
                                INSERT INTO utilisateur (role_id, prenom, nom, email, mot_de_passe, telephone, adresse_postale, ville, pays)
                                VALUES (1, :prenom, :nom, :email, :mdp, :telephone, :adresse, :ville, :pays)
                                ");
        $stmt->execute([
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':mdp' => $hash,
            ':telephone' => $telephone ?: null,
            ':adresse' => $adresse ?: null,
            ':ville' => $ville ?: null,
            ':pays' => $pays ?: null
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
                <div style='font-family: sans-serif; color: #333; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px;'>
                    <h1 style='color: #27ae60;'>Bienvenue $prenom !</h1>
                    <p>Ton compte a été créé avec succès sur <strong>Vite & Gourmand</strong>.</p>
                    <p>Tu peux maintenant te connecter pour passer ta première commande en cliquant sur le bouton ci-dessous :</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/vite-et-gourmand/pages/connexion.php' style='background-color: #27ae60; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                            Me connecter
                        </a>
                    </div>
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
$_SESSION['form_inscription'] = compact('prenom', 'nom', 'email', 'telephone', 'adresse', 'ville', 'pays');
header('Location: ../pages/inscription.php');
exit;
