<?php
use App\Core\View;
use App\Security\Csrf;

/** @var array $saisie  saisie précédente (sans les mots de passe), en cas d'erreur */
$valeur = fn(string $cle): string => htmlspecialchars((string) ($saisie[$cle] ?? ''));
?>

<section class="section-auth">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="auth-wrapper">

            <div class="auth-brand">
                <a href="<?= $rootPath ?>pages/accueil.php" class="auth-brand__link">
                    <span class="auth-brand__name">Vite <span class="auth-brand__amp">&</span> Gourmand</span>
                </a>
                <p class="auth-brand__tagline">Rejoignez-nous et savourez l'expérience.</p>
            </div>

            <div class="auth-card">
                <div class="auth-card__header">
                    <h1 class="auth-card__title">Créer un compte</h1>
                    <p class="auth-card__sub">Les champs marqués d'un <span class="auth-required">*</span> sont obligatoires.</p>
                </div>

                <form method="POST" action="../actions/inscription.php" class="auth-form">
                    <?= Csrf::champ() ?>

                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label for="prenom" class="auth-label">Prénom <span class="auth-required">*</span></label>
                            <input type="text" id="prenom" name="prenom" class="auth-input" placeholder="Marie" value="<?= $valeur('prenom') ?>" required autocomplete="given-name">
                        </div>
                        <div class="auth-field">
                            <label for="nom" class="auth-label">Nom <span class="auth-required">*</span></label>
                            <input type="text" id="nom" name="nom" class="auth-input" placeholder="Dupont" value="<?= $valeur('nom') ?>" required autocomplete="family-name">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="email" class="auth-label">Adresse e-mail <span class="auth-required">*</span></label>
                        <input type="email" id="email" name="email" class="auth-input" placeholder="votre@email.com" value="<?= $valeur('email') ?>" required autocomplete="email">
                    </div>

                    <div class="auth-field">
                        <label for="telephone" class="auth-label">Numéro de GSM <span class="auth-required">*</span></label>
                        <input type="tel" id="telephone" name="telephone" class="auth-input" placeholder="06 00 00 00 00" value="<?= $valeur('telephone') ?>" required autocomplete="tel">
                    </div>

                    <div class="auth-field">
                        <label for="adresse" class="auth-label">Adresse postale <span class="auth-required">*</span></label>
                        <input type="text" id="adresse" name="adresse" class="auth-input" placeholder="12 rue des Lilas" value="<?= $valeur('adresse') ?>" required autocomplete="street-address">
                    </div>

                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label for="ville" class="auth-label">Ville</label>
                            <input type="text" id="ville" name="ville" class="auth-input" placeholder="Bordeaux" value="<?= $valeur('ville') ?>" autocomplete="address-level2">
                        </div>
                        <div class="auth-field">
                            <label for="pays" class="auth-label">Pays</label>
                            <input type="text" id="pays" name="pays" class="auth-input" placeholder="France" value="<?= $valeur('pays') ?>" autocomplete="country-name">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="mot_de_passe" class="auth-label">Mot de passe <span class="auth-required">*</span></label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="auth-input" required autocomplete="new-password" minlength="10" aria-describedby="mdp-regles">
                        <?php View::partial('partials/regles-mot-de-passe'); ?>
                    </div>

                    <div class="auth-field">
                        <label for="mot_de_passe_conf" class="auth-label">Confirmer le mot de passe <span class="auth-required">*</span></label>
                        <input type="password" id="mot_de_passe_conf" name="mot_de_passe_conf" class="auth-input" required autocomplete="new-password">
                        <span class="mdp-match-msg" id="mdp-match-msg" aria-live="polite"></span>
                    </div>

                    <p class="auth-card__sub">
                        Vos données servent uniquement à gérer vos commandes. Vous pouvez les modifier ou supprimer votre compte à tout moment depuis votre espace
                        (<a href="<?= $rootPath ?>pages/mentions-legales.php" class="auth-link">en savoir plus</a>).
                    </p>

                    <button type="submit" class="auth-btn">Créer mon compte
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>

                <div class="auth-card__footer">
                    <p>Déjà un compte ? <a href="<?= $rootPath ?>pages/connexion.php" class="auth-link">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
