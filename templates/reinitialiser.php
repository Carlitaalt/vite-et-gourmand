<?php
use App\Core\View;
use App\Security\Csrf;

/**
 * @var string $jeton
 * @var bool $jetonValide
 */
?>

<section class="section-auth">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="auth-wrapper">

            <div class="auth-brand">
                <a href="<?= $rootPath ?>pages/accueil.php" class="auth-brand__link">
                    <span class="auth-brand__name">Vite <span class="auth-brand__amp">&</span> Gourmand</span>
                </a>
                <p class="auth-brand__tagline">Choisissez un nouveau mot de passe sécurisé.</p>
            </div>

            <div class="auth-card">
                <div class="auth-card__header">
                    <h1 class="auth-card__title">Nouveau mot de passe</h1>
                </div>

                <?php if (!$jetonValide): ?>
                    <p class="auth-alert auth-alert--error" role="alert">Ce lien est invalide ou a expiré.</p>
                    <div class="auth-card__footer">
                        <p><a href="<?= $rootPath ?>pages/mot-de-passe-oublie.php" class="auth-link">← Refaire une demande</a></p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="reintialiser.php?token=<?= htmlspecialchars(urlencode($jeton)) ?>" class="auth-form">
                        <?= Csrf::champ() ?>

                        <div class="auth-field">
                            <label for="mot_de_passe" class="auth-label">Nouveau mot de passe <span class="auth-required">*</span></label>
                            <input type="password" id="mot_de_passe" name="mot_de_passe" class="auth-input" required autocomplete="new-password" minlength="10" aria-describedby="mdp-regles">
                            <?php View::partial('partials/regles-mot-de-passe'); ?>
                        </div>

                        <div class="auth-field">
                            <label for="mot_de_passe_conf" class="auth-label">Confirmer le mot de passe <span class="auth-required">*</span></label>
                            <input type="password" id="mot_de_passe_conf" name="mot_de_passe_conf" class="auth-input" required autocomplete="new-password">
                            <span class="mdp-match-msg" id="mdp-match-msg" aria-live="polite"></span>
                        </div>

                        <button type="submit" class="auth-btn">Enregistrer le mot de passe</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
