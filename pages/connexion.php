<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';

if(est_connecte()){
    header('Location: ../pages/accueil.php');
    exit;
}

$pageTitle = 'Connexion';
$rootPath = '../';
$currentPage = 'connexion';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$erreur = $_SESSION['erreur_connexion'] ?? '';
$succes = $_SESSION['succes_inscription'] ?? '';
$email = $_SESSION['email_saisi'] ?? '';

//Nettoyer les messages après affichage (flash messages)
unset($_SESSION['erreur_connexion'], $_SESSION['succes_inscription'], $_SESSION['email_saisi']);
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
            <p class="auth-brand__tagline">
                Bienvenue, bon retour parmi nous.
            </p>
         </div>

         <!-- CARTE FORMULAIRE -->
          <div class="auth-card">
            <div class="auth-card__header">
                <h1 class="auth-card__title">Connexion</h1>
                <p class="auth-card__sub">Accédez à votre espace personnel</p>
            </div>

            <!-- MESSAGE ERREUR -->
             <?php if ($erreur): ?>
                <div class="auth-alert auth-alert--error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($erreur) ?>
                </div>
                <?php endif; ?>

                <?php if ($succes): ?>
                    <div class="auth-alert auth-alert--success">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        <?= htmlspecialchars($succes) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="../actions/connexion.php" class="auth-form" novalidate>
                        
                        <div class="auth-field">
                            <label for="email" class="auth-label">Adresse e-mail</label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                <input type="email" id="email" name="email" class="auth-input" placeholder="votre@email.com" value="<?= htmlspecialchars($email) ?>" required autocomplete="email" autofocus>
                            </div>
                        </div>
                        
                        <div class="auth-field">
                            <label for="mot_de_passe" class="auth-label">Mot de passe</label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" class="auth-input" placeholder="•••••••" required autocomplete="current-password">
                            </div>
                            <a href="<?= $rootPath ?>pages/mot-de-passe-oublie.php" class="auth-link auth-link--small">Mot de passe oublié ?</a>
                        </div>

                        <button type="submit" class="auth-btn">Se connecter
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </form>

                    <div class="auth-card__footer">
                        <p>Pas encore de compte ? <a href="<?= $rootPath ?>pages/inscription.php" class="auth-link">Créer un compte</a></p>
                    </div>
          </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>