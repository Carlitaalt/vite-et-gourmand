<?php
use App\Security\Csrf;
?>

<section class="section-auth">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="auth-wrapper">

            <div class="auth-brand">
                <a href="<?= $rootPath ?>pages/accueil.php" class="auth-brand__link">
                    <span class="auth-brand__name">Vite <span class="auth-brand__amp">&</span> Gourmand</span>
                </a>
                <p class="auth-brand__tagline">Pas de panique, ça arrive à tout le monde.</p>
            </div>

            <div class="auth-card">
                <div class="auth-card__header">
                    <h1 class="auth-card__title">Mot de passe oublié</h1>
                    <p class="auth-card__sub">Saisissez votre adresse e-mail : vous recevrez un lien pour choisir un nouveau mot de passe.</p>
                </div>

                <form method="POST" action="mot-de-passe-oublie.php" class="auth-form">
                    <?= Csrf::champ() ?>
                    <div class="auth-field">
                        <label for="email" class="auth-label">Adresse e-mail</label>
                        <input type="email" id="email" name="email" class="auth-input" placeholder="votre@email.com" required autocomplete="email">
                    </div>
                    <button type="submit" class="auth-btn">Envoyer le lien</button>
                </form>

                <div class="auth-card__footer">
                    <p><a href="<?= $rootPath ?>pages/connexion.php" class="auth-link">← Retour à la connexion</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
