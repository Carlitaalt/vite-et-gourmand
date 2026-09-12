<?php
use App\Security\Csrf;

/** @var array $saisie  saisie précédente, en cas d'erreur */
$valeur = fn(string $cle): string => htmlspecialchars((string) ($saisie[$cle] ?? ''));
?>

<section class="section-auth section-contact">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="auth-wrapper contact-wrapper">

            <div class="auth-brand">
                <a href="<?= $rootPath ?>pages/accueil.php" class="auth-brand__link">
                    <span class="auth-brand__name">Vite <span class="auth-brand__amp">&</span> Gourmand</span>
                </a>
                <p class="auth-brand__tagline">Une question ? Nous sommes à votre écoute.</p>
            </div>

            <div class="auth-card">
                <div class="auth-card__header">
                    <h1 class="auth-card__title">Nous contacter</h1>
                    <p class="auth-card__sub">Remplissez le formulaire, nous vous répondrons par e-mail.</p>
                </div>

                <form method="POST" action="contact.php" class="auth-form">
                    <?= Csrf::champ() ?>

                    <div class="auth-field">
                        <label for="email" class="auth-label">Votre adresse e-mail <span class="auth-required">*</span></label>
                        <input type="email" id="email" name="email" class="auth-input" placeholder="votre@email.com" value="<?= $valeur('email') ?>" required autocomplete="email">
                    </div>

                    <div class="auth-field">
                        <label for="titre" class="auth-label">Titre du message <span class="auth-required">*</span></label>
                        <input type="text" id="titre" name="titre" class="auth-input" placeholder="Ex : demande de devis pour un mariage" value="<?= $valeur('titre') ?>" maxlength="150" required>
                    </div>

                    <div class="auth-field">
                        <label for="description" class="auth-label">Votre message <span class="auth-required">*</span></label>
                        <textarea id="description" name="description" class="auth-input contact-textarea" placeholder="Décrivez votre demande en détail..." rows="5" maxlength="5000" required><?= $valeur('description') ?></textarea>
                    </div>

                    <button type="submit" class="auth-btn">Envoyer le message
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>

                <div class="contact-infos">
                    <div class="contact-info-item">
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <span>06 00 00 00 00</span>
                    </div>
                    <div class="contact-info-item">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <span>contact@vite-gourmand.fr</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
