<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$pageTitle = 'Contact';
$rootPath = '../';
$currentPage = 'contact';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$erreur = '';
$succes = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $email = trim($_POST['email'] ?? '');

    //Validation
    if(empty($titre) || empty($description) || empty($email)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse e-mail invalide.';
    } else {
        $mail = new PHPMailer(true);

        try {
            //Paramêtre du serveur Mailtrap
            $mail->isSMTP();

            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'a42fbdd3effc59';
            $mail->Password = '6bbe288f32e485';
            $mail->Port = 2525;

            //Destinataires
            $mail->setFrom('noreply@viteetgourmand.fr', 'Vite & Gourmand');
            $mail->addAddress('contact@viteetgourmand.fr');
            $mail->addReplyTo($email);

            //Contenu
            $mail->isHtml(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '[Contact] ' . $titre;

            //Construction du texte du mail
            $contenuMail = "Nouveau message de : " . $email . "\n";
            $contenuMail .= "Sujet : " . $titre . "\n";
            $contenuMail .= "-------------------------------------------\n\n";
            $contenuMail .= $description;

            $mail->Body = $contenuMail;

            $mail->send();
            $succes = 'Votre message a bien été envoyé !';
        } catch (Exception $e) {
            $erreur = "Le message n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
        }
    }
}
?>

<section class="section-auth section-contact">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="auth-wrapper contact-wrapper">

        <!--Marque-->
        <div class="auth-brand">
            <a href="<?= $rootPath ?>pages/accueil.php" class="auth-brand__link">
                <span class="auth-brand__name">Vite <span class="auth-brand__amp">&</span> Gourmand</span>
            </a>
            <p class="auth-brand__tagline">Une question ? Nous sommes à votre écoute.</p>
        </div>

        <!-- Carte formulaire -->
         <div class="auth-card">

         <div class="auth-card__header">
            <h1 class="auth-card__title">Nous contacter</h1>
            <p class="auth-card__sub">Remplissez le formulaire, nous vous répondrons rapidement</p>
         </div>

         <!-- Alerte erreur -->
          <?php if($erreur): ?>
            <div class="auth-alert auth-alert--error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($erreur) ?>
            </div>
            <?php endif; ?>

        <!-- Alerte succès -->
          <?php if($succes): ?>
            <div class="auth-alert auth-alert--success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($succes) ?>
            </div>
            <?php endif; ?>

            <?php if(empty($succes)): ?>
                <form method="POST" action="" class="auth-form" novalidate>

                <!-- Email -->
                 <div class="auth-field">
                    <label for="email" class="auth-label">Votre adresse e-mail <span class="auth-required">*</span></label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" id="email" name="email" class="auth-input" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
                    </div>
                 </div>

                 <!-- Titre -->
                  <div class="auth-field">
                    <label for="titre" class="auth-label">Titre du message <span class="auth-required">*</span></label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <input type="text" id="titre" name="titre" class="auth-input" placeholder="Ex: Demande de devis pour un mariage" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
                    </div>
                  </div>

                  <!-- Description -->
                   <div class="auth-field">
                    <label for="description" class="auth-label">Votre message <span class="auth-required">*</span></label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon contact-textarea-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <textarea name="description" id="description" class="auth-input contact-textarea" placeholder="Décrivez votre demande en détail..." required rows="5"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                   </div>

                   <button type="submit" class="auth-btn">Envoyer le message
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                   </button>
                </form>
                <?php endif; ?>

                <!-- Info contact -->
                 <div class="contact-infos">
                    <div class="contact-info-item">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></svg>
                        <span>06 00 00 00 00</span>
                    </div>
                    <div class="contact-info-item">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span>contact@viteetgourmand.fr</span>
                    </div>
                 </div>

        </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>