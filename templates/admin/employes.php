<?php
use App\Core\View;
use App\Security\Csrf;

/**
 * Gestion des comptes employés (administrateur uniquement).
 * Aucun formulaire ne permet de créer un compte administrateur.
 *
 * @var App\Entity\Employe[] $employes
 */
$page = 'espace-admin.php';
?>

<div class="admin-section-header">
    <h2 class="employe-section-titre">Comptes employés</h2>
    <button type="button" class="btn-compte-action btn-compte-action--modifier" data-bascule="form-nouvel-employe" aria-expanded="false" aria-controls="form-nouvel-employe">+ Créer un compte employé</button>
</div>

<div class="commande-modif-form admin-form-employe" id="form-nouvel-employe" hidden>
    <form method="POST" action="<?= $page ?>" class="modif-form">
        <?= Csrf::champ() ?>
        <input type="hidden" name="action" value="creer_employe">
        <h3 class="modif-form__titre">Nouveau compte employé</h3>
        <p class="admin-notice">
            L'employé recevra un e-mail l'informant de la création de son compte.
            <strong>Le mot de passe ne lui est pas envoyé</strong> : il devra vous le demander directement.
        </p>

        <div class="commande-field-row">
            <div class="commande-field">
                <label for="employe-prenom" class="commande-label">Prénom <span class="auth-required">*</span></label>
                <input type="text" id="employe-prenom" name="prenom" class="commande-input" required>
            </div>
            <div class="commande-field">
                <label for="employe-nom" class="commande-label">Nom <span class="auth-required">*</span></label>
                <input type="text" id="employe-nom" name="nom" class="commande-input" required>
            </div>
        </div>
        <div class="commande-field-row">
            <div class="commande-field">
                <label for="employe-email" class="commande-label">Adresse e-mail (identifiant) <span class="auth-required">*</span></label>
                <input type="email" id="employe-email" name="email" class="commande-input" required>
            </div>
            <div class="commande-field">
                <label for="employe-poste" class="commande-label">Poste</label>
                <input type="text" id="employe-poste" name="poste" class="commande-input" placeholder="Ex : chef de cuisine">
            </div>
        </div>
        <div class="commande-field-row">
            <div class="commande-field">
                <label for="employe-tel" class="commande-label">Téléphone</label>
                <input type="tel" id="employe-tel" name="telephone" class="commande-input">
            </div>
            <div class="commande-field">
                <label for="employe-salaire" class="commande-label">Salaire horaire (€)</label>
                <input type="number" id="employe-salaire" name="salaire" class="commande-input" step="0.01" min="0">
            </div>
        </div>
        <div class="commande-field-row">
            <div class="commande-field">
                <label for="employe-adresse" class="commande-label">Adresse postale</label>
                <input type="text" id="employe-adresse" name="adresse" class="commande-input">
            </div>
            <div class="commande-field">
                <label for="employe-ville" class="commande-label">Ville</label>
                <input type="text" id="employe-ville" name="ville" class="commande-input">
            </div>
        </div>
        <div class="commande-field-row">
            <div class="commande-field">
                <label for="mot_de_passe" class="commande-label">Mot de passe <span class="auth-required">*</span></label>
                <input type="password" id="mot_de_passe" name="password" class="commande-input" required minlength="10" autocomplete="new-password" aria-describedby="mdp-regles">
                <?php View::partial('partials/regles-mot-de-passe'); ?>
            </div>
            <div class="commande-field">
                <label for="mot_de_passe_conf" class="commande-label">Confirmer le mot de passe <span class="auth-required">*</span></label>
                <input type="password" id="mot_de_passe_conf" name="password_confirm" class="commande-input" required autocomplete="new-password">
                <span class="mdp-match-msg" id="mdp-match-msg" aria-live="polite"></span>
            </div>
        </div>
        <div class="modif-form__actions">
            <button type="submit" class="btn btn-vg-primary">Créer le compte</button>
            <button type="button" class="btn btn-vg-secondary" data-bascule="form-nouvel-employe">Annuler</button>
        </div>
    </form>
</div>

<div class="admin-employes-liste">
    <?php if (empty($employes)): ?>
        <div class="compte-empty"><p>Aucun compte employé pour le moment.</p></div>
    <?php endif; ?>

    <?php foreach ($employes as $employe):
        $compte = $employe->getUtilisateur();
        $id = $compte->getId();
    ?>
        <div class="admin-employe-card <?= $compte->estActif() ? '' : 'admin-employe-card--inactif' ?>">
            <div class="admin-employe-card__left">
                <div class="employe-client-avatar <?= $compte->estActif() ? '' : 'avatar--inactif' ?>" aria-hidden="true"><?= htmlspecialchars($compte->getInitiales()) ?></div>
                <div class="admin-employe-info-wrap">
                    <h3 class="admin-employe-nom">
                        <?= htmlspecialchars($compte->getNomComplet()) ?>
                        <span class="commande-statut <?= $compte->estActif() ? 'statut--accepte' : 'statut--annule' ?>"><?= $compte->estActif() ? 'Actif' : 'Désactivé' ?></span>
                    </h3>
                    <div class="admin-employe-meta">
                        <span><?= htmlspecialchars($compte->getEmail()) ?></span>
                        <span aria-hidden="true">·</span>
                        <span><?= htmlspecialchars($employe->getPoste()) ?></span>
                        <?php if ($employe->getSalaireHoraire() !== null): ?>
                            <span aria-hidden="true">·</span>
                            <span><?= number_format($employe->getSalaireHoraire(), 2, ',', ' ') ?> €/h</span>
                        <?php endif; ?>
                    </div>
                    <div class="admin-employe-dates">
                        Membre depuis le <?= $compte->getCreeLe()?->format('d/m/Y') ?>
                        · Dernière modification : <?= $compte->getModifieLe()?->format('d/m/Y à H:i') ?>
                    </div>
                </div>
            </div>

            <div class="admin-employe-card__actions">
                <button type="button" class="btn-compte-action btn-compte-action--modifier" data-bascule="edition-employe-<?= $id ?>" aria-expanded="false" aria-controls="edition-employe-<?= $id ?>">
                    ✎ Modifier<span class="visually-hidden"> la fiche de <?= htmlspecialchars($compte->getNomComplet()) ?></span>
                </button>

                <!-- Désactivation : l'employé ne peut plus se connecter (départ de l'entreprise) -->
                <form method="POST" action="<?= $page ?>" class="d-inline" data-confirmation="<?= $compte->estActif() ? 'Désactiver' : 'Réactiver' ?> le compte de <?= htmlspecialchars($compte->getNomComplet()) ?> ?">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="action" value="toggle_employe">
                    <input type="hidden" name="employe_id" value="<?= $id ?>">
                    <input type="hidden" name="actif" value="<?= $compte->estActif() ? '0' : '1' ?>">
                    <button type="submit" class="btn-compte-action <?= $compte->estActif() ? 'btn-compte-action--annuler' : 'btn-compte-action--modifier' ?>">
                        <?= $compte->estActif() ? '✕ Désactiver' : '✓ Réactiver' ?><span class="visually-hidden"> le compte de <?= htmlspecialchars($compte->getNomComplet()) ?></span>
                    </button>
                </form>
            </div>

            <div class="commande-modif-form w-100" id="edition-employe-<?= $id ?>" hidden>
                <form method="POST" action="<?= $page ?>" class="modif-form">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="action" value="modifier_employe">
                    <input type="hidden" name="employe_id" value="<?= $id ?>">
                    <div class="commande-field-row">
                        <div class="commande-field">
                            <label for="poste-<?= $id ?>" class="commande-label">Poste</label>
                            <input type="text" id="poste-<?= $id ?>" name="poste" class="commande-input" value="<?= htmlspecialchars($employe->getPoste()) ?>" required>
                        </div>
                        <div class="commande-field">
                            <label for="salaire-<?= $id ?>" class="commande-label">Salaire horaire (€)</label>
                            <input type="number" id="salaire-<?= $id ?>" name="salaire" class="commande-input" step="0.01" min="0" value="<?= $employe->getSalaireHoraire() ?? '' ?>">
                        </div>
                    </div>
                    <div class="modif-form__actions">
                        <button type="submit" class="btn btn-vg-primary">Enregistrer</button>
                        <button type="button" class="btn btn-vg-secondary" data-bascule="edition-employe-<?= $id ?>">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
