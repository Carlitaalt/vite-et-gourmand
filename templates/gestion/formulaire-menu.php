<?php
use App\Security\Csrf;

/**
 * Formulaire de création (menu = null) ou de modification d'un menu.
 *
 * @var App\Entity\Menu|null $menu
 * @var App\Entity\Theme[] $themes
 * @var App\Entity\Regime[] $regimes
 * @var string $page
 */
$suffixe = $menu?->getId() ?? 'nouveau';
$idBloc = $menu ? 'form-menu-' . $menu->getId() : 'form-nouveau-menu';
?>
<form method="POST" action="<?= $page ?>" class="modif-form" enctype="multipart/form-data">
    <?= Csrf::champ() ?>
    <input type="hidden" name="action" value="update_menu">
    <?php if ($menu): ?>
        <input type="hidden" name="menu_id" value="<?= $menu->getId() ?>">
    <?php endif; ?>

    <h3 class="modif-form__titre"><?= $menu ? 'Modifier le menu' : 'Nouveau menu' ?></h3>

    <div class="commande-field">
        <label for="menu-titre-<?= $suffixe ?>" class="commande-label">Titre <span class="auth-required">*</span></label>
        <input type="text" id="menu-titre-<?= $suffixe ?>" name="titre" class="commande-input" value="<?= htmlspecialchars($menu?->getTitre() ?? '') ?>" required>
    </div>

    <div class="commande-field">
        <label for="menu-description-<?= $suffixe ?>" class="commande-label">Description</label>
        <textarea id="menu-description-<?= $suffixe ?>" name="description" class="commande-input avis-textarea" rows="3"><?= htmlspecialchars($menu?->getDescription() ?? '') ?></textarea>
    </div>

    <div class="commande-field">
        <label for="menu-conditions-<?= $suffixe ?>" class="commande-label">Conditions (délai de commande, conservation...)</label>
        <textarea id="menu-conditions-<?= $suffixe ?>" name="conditions" class="commande-input avis-textarea" rows="2" placeholder="Ex : à commander 5 jours avant la prestation"><?= htmlspecialchars($menu?->getConditions() ?? '') ?></textarea>
    </div>

    <div class="commande-field-row">
        <div class="commande-field">
            <label for="menu-theme-<?= $suffixe ?>" class="commande-label">Thème</label>
            <select id="menu-theme-<?= $suffixe ?>" name="theme_id" class="commande-input">
                <?php foreach ($themes as $theme): ?>
                    <option value="<?= $theme->getId() ?>" <?= $menu?->getTheme()->getId() === $theme->getId() ? 'selected' : '' ?>><?= htmlspecialchars($theme->getLibelle()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="commande-field">
            <label for="menu-regime-<?= $suffixe ?>" class="commande-label">Régime</label>
            <select id="menu-regime-<?= $suffixe ?>" name="regime_id" class="commande-input">
                <?php foreach ($regimes as $regime): ?>
                    <option value="<?= $regime->getId() ?>" <?= $menu?->getRegime()->getId() === $regime->getId() ? 'selected' : '' ?>><?= htmlspecialchars($regime->getLibelle()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="commande-field-row">
        <div class="commande-field">
            <label for="menu-prix-<?= $suffixe ?>" class="commande-label">Prix par personne (€) <span class="auth-required">*</span></label>
            <input type="number" id="menu-prix-<?= $suffixe ?>" name="prix" class="commande-input" step="0.5" min="0.5" value="<?= $menu?->getPrixParPersonne() ?? '' ?>" required>
        </div>
        <div class="commande-field">
            <label for="menu-min-<?= $suffixe ?>" class="commande-label">Personnes minimum <span class="auth-required">*</span></label>
            <input type="number" id="menu-min-<?= $suffixe ?>" name="nombre_personne_minimum" class="commande-input" min="1" value="<?= $menu?->getNombrePersonneMinimum() ?? 1 ?>" required>
        </div>
        <div class="commande-field">
            <label for="menu-stock-<?= $suffixe ?>" class="commande-label">Stock (commandes possibles) <span class="auth-required">*</span></label>
            <input type="number" id="menu-stock-<?= $suffixe ?>" name="stock_disponible" class="commande-input" min="0" value="<?= $menu?->getStockDisponible() ?? 10 ?>" required>
        </div>
    </div>

    <div class="commande-field-row">
        <div class="commande-field">
            <label for="menu-actif-<?= $suffixe ?>" class="commande-label">Statut</label>
            <select id="menu-actif-<?= $suffixe ?>" name="actif" class="commande-input">
                <option value="1" <?= $menu === null || $menu->estActif() ? 'selected' : '' ?>>Actif (visible sur le site)</option>
                <option value="0" <?= $menu !== null && !$menu->estActif() ? 'selected' : '' ?>>Inactif (masqué)</option>
            </select>
        </div>
        <div class="commande-field">
            <label for="menu-photos-<?= $suffixe ?>" class="commande-label"><?= $menu ? 'Ajouter des photos' : 'Photos' ?></label>
            <input type="file" id="menu-photos-<?= $suffixe ?>" name="menu_photo[]" class="commande-input" accept="image/jpeg,image/png,image/webp" multiple aria-describedby="menu-photos-aide-<?= $suffixe ?>">
            <p class="commande-nb-hint" id="menu-photos-aide-<?= $suffixe ?>">JPG, PNG ou WEBP, 5 Mo maximum par photo.</p>
        </div>
    </div>

    <div class="modif-form__actions">
        <button type="submit" class="btn btn-vg-primary"><?= $menu ? 'Enregistrer' : 'Créer le menu' ?></button>
        <button type="button" class="btn btn-vg-secondary" data-bascule="<?= $idBloc ?>">Annuler</button>
    </div>
</form>
