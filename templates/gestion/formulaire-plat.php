<?php
use App\Entity\Plat;
use App\Security\Csrf;

/**
 * Formulaire de création (plat = null) ou de modification d'un plat.
 * Un plat peut être rattaché à plusieurs menus (cases à cocher).
 *
 * @var Plat|null $plat
 * @var App\Entity\Menu[] $menus
 * @var App\Entity\Allergene[] $allergenes
 * @var string $page
 */
$suffixe = $plat?->getId() ?? 'nouveau';
$idBloc = $plat ? 'form-plat-' . $plat->getId() : 'form-nouveau-plat';
$menusDuPlat = $plat?->getMenuIds() ?? [];
$allergenesDuPlat = $plat?->getAllergeneIds() ?? [];
?>
<form method="POST" action="<?= $page ?>" class="modif-form">
    <?= Csrf::champ() ?>
    <input type="hidden" name="action" value="update_plat">
    <?php if ($plat): ?>
        <input type="hidden" name="plat_id" value="<?= $plat->getId() ?>">
    <?php endif; ?>

    <h3 class="modif-form__titre"><?= $plat ? 'Modifier le plat' : 'Nouveau plat' ?></h3>

    <div class="commande-field-row">
        <div class="commande-field">
            <label for="plat-titre-<?= $suffixe ?>" class="commande-label">Nom du plat <span class="auth-required">*</span></label>
            <input type="text" id="plat-titre-<?= $suffixe ?>" name="titre_plat" class="commande-input" value="<?= htmlspecialchars($plat?->getTitre() ?? '') ?>" required>
        </div>
        <div class="commande-field">
            <label for="plat-categorie-<?= $suffixe ?>" class="commande-label">Catégorie</label>
            <select id="plat-categorie-<?= $suffixe ?>" name="categorie" class="commande-input">
                <?php foreach (Plat::CATEGORIES as $categorie): ?>
                    <option value="<?= $categorie ?>" <?= $plat?->getCategorie() === $categorie ? 'selected' : '' ?>><?= $categorie ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="commande-field">
        <label for="plat-description-<?= $suffixe ?>" class="commande-label">Description</label>
        <textarea id="plat-description-<?= $suffixe ?>" name="description" class="commande-input avis-textarea" rows="2"><?= htmlspecialchars($plat?->getDescription() ?? '') ?></textarea>
    </div>

    <fieldset class="commande-field">
        <legend class="commande-label">Menus dans lesquels ce plat apparaît</legend>
        <div class="choix-multiples">
            <?php foreach ($menus as $menu): ?>
                <label>
                    <input type="checkbox" name="menus[]" value="<?= $menu->getId() ?>" <?= in_array($menu->getId(), $menusDuPlat, true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($menu->getTitre()) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <fieldset class="commande-field">
        <legend class="commande-label">Allergènes présents</legend>
        <div class="choix-multiples">
            <?php foreach ($allergenes as $allergene): ?>
                <label>
                    <input type="checkbox" name="allergenes[]" value="<?= $allergene->getId() ?>" <?= in_array($allergene->getId(), $allergenesDuPlat, true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($allergene->getLibelle()) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="commande-field">
        <label for="plat-actif-<?= $suffixe ?>" class="commande-label">Statut</label>
        <select id="plat-actif-<?= $suffixe ?>" name="actif" class="commande-input">
            <option value="1" <?= $plat === null || $plat->estActif() ? 'selected' : '' ?>>Actif</option>
            <option value="0" <?= $plat !== null && !$plat->estActif() ? 'selected' : '' ?>>Inactif</option>
        </select>
    </div>

    <div class="modif-form__actions">
        <button type="submit" class="btn btn-vg-primary"><?= $plat ? 'Enregistrer' : 'Ajouter le plat' ?></button>
        <button type="button" class="btn btn-vg-secondary" data-bascule="<?= $idBloc ?>">Annuler</button>
    </div>
</form>
