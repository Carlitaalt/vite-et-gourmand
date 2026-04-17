<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$pageTitle = 'Mot de passe oublié';
$rootPath = '../';
$currentPage = 'connexion';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$erreur = '';
$succes = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if(empty($email)) {
        $erreur = 'Veuillez saisir votre adresse e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse e-mail invalide.';
    } else {
        try {
            //Vérifier si l'email eviste en base
            $stmt = $pdo->prepare("SELECT utilisateur_id, prenom FROM utilisateur WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if($user) {
                // Générer un token sécurisé unique
                $token = bin2hex(random_bytes(32)); //64 caractères hex
                $expireAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                //Stocker le token en BDD
                //Table attendue: password_reset_tokens (utilisateur_id, token, expire_at, used)
                $pdo->prepare("DELETE FROM password_reset_tokens WHERE utilisateur_id = :uid")
                ->execute([':uid' => $user['utilisateur_id']]);

                $ins = $pdo->prepare("
                INSERT INTO password_reset_tokens (utilisateur_id, token, expire_at, used)
                VALUES (:uid, :token, :expire, 0)
                ");
                $ins->execute([
                    ':uid' => $user['utilisateur_id'],
                    ':token' => $token,
                    ':expire' => $expireAt,
                ]);

                //Construire le lien de réintialisation
                $lien = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                        . '://' . $_SERVER['HTTP_HOST']
                        . dirname($_SERVER['REQUEST_URI'])
                        . '/reintialiser.php?token=' . $token;

                //Envoi du mail via PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'sandbox.smtp.mailtrap.io';
                    $mail->SMTPAuth = true;
                    $mail->Port = 2525;
                    $mail->Username = 'a42fbdd3effc59';
                    $mail->Password = '6bbe288f32e485';
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('noreply@viteetgourmand.fr', 'Vite & Gourmand');
                    $mail->addAddress($email, $user['prenom']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Réintialisation de votre mot de passe.';
                    $mail->Body = "Bonjour {$user['prenom']}, <br><br>
                                    Cliquez sur le lien pour changer votre mot de passe
                                    <a href='$lien'>$lien</a><br><br>
                                    Ce lien expire dans 1 heure.";
                    $mail->send();
                } catch (Exception $e){
                    error_log("Erreur mail: " . $mail->ErrorInfo);
                }
    }

    //Message de sécurité
    $succes = 'Si cette adresse est associé à un compte, vous recevrez un e-mail sous peu.';
} catch (Exception $e){
    error_log($e->getMessage());
    $erreur = 'Une erreur est survenue. Veuillez réessayer.';
    }
    }
}
?>

<section class="section-auth">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="auth-wrapper">

        <!-- LOGO -->
         <div class="auth-brand">
            <a href="<?= $rootPath ?>pages/accueil.php" class="auth-brand__link">
                <span class="auth-brand__name">Vite <span class="auth-brand__amp">&</span> Gourmand</span>
            </a>
            <p class="auth-brand__tagline">Pas de panique, ça arrive à tout le monde.</p>
         </div>

         <!-- CARTE FORMULAIRE -->
          <div class="auth-card">
            <div class="auth-card__header">
                <h1 class="auth-card__title">Mot de passe oublié</h1>
                <p class="auth-card__sub">Saisissez votre e-mail pour recevoir un lien de réintialisation</p>
            </div>

            <!-- ALERTE ERREUR -->
             <?php if($erreur): ?>
                <div class="auth-alert auth-alert--error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($erreur) ?>
                </div>
                <?php endif; ?>

                <!-- ALERTE SUCCÈS -->
                 <?php if ($succes): ?>
                    <div class="auth-alert auth-alert--success">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <?= htmlspecialchars($succes) ?>
                    </div>
                    <div class="auth-card__footer">
                        <p>
                            <a href="<?= $rootPath ?>pages/connexion.php" class="auth-link">
                                ← Retour à la connexion
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- FORMULAIRE (masqué après succès) -->
                     <?php if (empty($succes)): ?>
                        <form method="POST" action="" class="auth-form" novalidate>
                            <div class="auth-field">
                                <label for="email" class="auth-label">Adresse e-mail</label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <polyline points="22,6 12,13 2,6"/>
                                    </svg>
                                    <input type="email" id="email" name="email" class="auth-input" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
                                </div>
                            </div>
                            <button type="submit" class="auth-btn">
                                Envoyez le lien
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="22" y1="2" x2="11" y2="13"/>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                </svg>
                            </button>
                        </form>
                        
                        <div class="auth-card__footer">
                            <p>
                                <a href="<?= $rootPath ?>pages/connexion.php" class="auth-link">Se connecter </a>
                            </p>
                        </div>
                        <?php endif; ?>

          </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

