<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if(est_connecte()) {
    header('Location: ../pages/accueil.php');
    exit;
}

$pageTitle = 'Inscription';
$rootPath = '../';
$currentPage = 'inscription';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$erreur = $_SESSION['erreur_inscription'] ?? '';
$succes = $_SESSION['succes_inscription'] ?? '';
$form = $_SESSION['form_inscription'] ?? [];
unset($_SESSION['erreur_inscription'], $_SESSION['succes_inscription'], $_SESSION['form_inscription']);

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
            <p class="auth-brand__tagline">Rejoignez-nous et savourez l'expérience.</p>
         </div>

         <!-- CARTE FORMUALIRE -->
          <div class="auth-card">

          <div class="auth-card__header">
            <h1 class="auth-card__title">Créer un compte</h1>
            <p class="auth-card__sub">Quelques informations pour commencer</p>
          </div>

          <?php if($erreur): ?>
            <div class="auth-alert auth-alert--error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($erreur) ?>
            </div>
            <?php endif; ?>

            <?php if($succes): ?>
                <div class="auth-alert auth-alert--success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <?= htmlspecialchars($succes) ?>

                    <a href="<?= $rootPath ?>pages/connexion.php" class="auth-link ms-1">Se connecter</a>
                </div>
                <?php endif; ?>

                <form method="POST" action="../actions/inscription.php" class="auth-form" novalidate>

                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label for="prenom" class="auth-label">Prénom <span class="auth-required">*</span></label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <input type="text" id="prenom" name="prenom" class="auth-input" placeholder="Marie" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required autocomplete="given-name">
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="nom" class="auth-label">Nom <span class="auth-required">*</span></label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <input type="text" id="nom" name="nom" class="auth-input" placeholder="Dupont" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required autocomplete="family-name">
                            </div>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="email" class="auth-label">Adresse mail <span class="auth-required">*</span></label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <input type="email" id="email" name="email" class="auth-input" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="telephone" class="auth-label">Téléphone</label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></svg>
                            <input type="tel" id="telephone" name="telephone" class="auth-input" placeholder="06 00 00 00 00" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" autocomplete="tel">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="adresse" class="auth-label">Adresse</label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <input type="text" id="adresse" name="adresse" class="auth-input" placeholder="12 rue des Lilas, 75001 Paris" value="<?= htmlspecialchars($form['adresse'] ?? '') ?>" autocomplete="street-address">
                        </div>
                    </div>
                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label for="ville" class="auth-label">Ville</label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg>
                                <input type="text" id="ville" name="ville" class="auth-input" placeholder="Bordeaux" value="<?= htmlspecialchars($form['ville'] ?? '') ?>" autocomplete="address-level2">
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="pays" class="auth-label">Pays</label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                <input type="text" id="pays" name="pays" class="auth-input" placeholder="France" value="<?= htmlspecialchars($form['pays'] ?? '') ?>" autocomplete="country-name">
                            </div>
                        </div>
                    </div>

                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label for="mot_de_passe" class="auth-label">Mot de passe <span class="auth-required">*</span></label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" class="auth-input" placeholder="••••••••" required autocomplete="new-password" minlength="8">
                            </div>
                        </div>
                            <div class="auth-field">
                                <label for="mot_de_passe_conf" class="auth-label">Confirmer le mot de passe <span class="auth-required">*</span></label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <input type="password" id="mot_de_passe_conf" name="mot_de_passe_conf" class="auth-input" placeholder="••••••••" required autocomplete="new-password">
                                </div>
                            </div>
                    </div>
                    <button type="submit" class="auth-btn">Créer mon compte
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>
                
                <div class="auth-card__footer">
                    <p>Déjà un compte ? <a href="<?= $rootPath ?>pages/connexion.php" class="auth-link">Se connecter</a></p>
                </div>
          </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

