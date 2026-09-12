<?php
use App\Core\View;
use App\Security\Csrf;

/**
 * @var App\Entity\Menu[] $menus
 * @var App\Entity\Plat[] $plats
 * @var App\Entity\Theme[] $themes
 * @var App\Entity\Regime[] $regimes
 * @var App\Entity\Allergene[] $allergenes
 * @var string $currentPage
 */
$page = $currentPage . '.php';
$formulaire = ['page' => $page, 'themes' => $themes, 'regimes' => $regimes, 'menus' => $menus, 'allergenes' => $allergenes];
?>

<div class="employe-sous-tabs" role="tablist" aria-label="Catalogue">
    <button type="button" class="employe-sous-tab active" role="tab" id="sous-onglet-menus" data-sous="sous-menus" aria-controls="sous-menus" aria-selected="true">Menus</button>
    <button type="button" class="employe-sous-tab" role="tab" id="sous-onglet-plats" data-sous="sous-plats" aria-controls="sous-plats" aria-selected="false">Plats</button>
</div>

<!-- MENUS -->
<div class="employe-sous-panel active" id="sous-menus" role="tabpanel" aria-labelledby="sous-onglet-menus">
    <div class="employe-catalogue-header">
        <h2 class="employe-section-titre">Gestion des menus</h2>
        <button type="button" class="btn-compte-action btn-compte-action--modifier" data-bascule="form-nouveau-menu" aria-expanded="false" aria-controls="form-nouveau-menu">+ Nouveau menu</button>
    </div>

    <div class="commande-modif-form" id="form-nouveau-menu" hidden>
        <?php View::partial('gestion/formulaire-menu', [...$formulaire, 'menu' => null]); ?>
    </div>

    <div class="employe-catalogue">
        <?php foreach ($menus as $menu): $id = $menu->getId(); ?>
            <div class="employe-catalogue-item <?= $menu->estActif() ? '' : 'item--inactif' ?>">
                <div class="employe-catalogue-item__header">
                    <div class="employe-catalogue-item__main">
                        <?php if ($menu->getImagePrincipale()): ?>
                            <img src="<?= $rootPath . htmlspecialchars($menu->getImagePrincipale()) ?>" alt="" class="menu-vignette-admin">
                        <?php else: ?>
                            <div class="menu-vignette-placeholder">Aucune image</div>
                        <?php endif; ?>
                        <div>
                            <h3 class="commande-item__num"><?= htmlspecialchars($menu->getTitre()) ?></h3>
                            <span class="commande-statut <?= $menu->estActif() ? 'statut--accepte' : 'statut--annule' ?>"><?= $menu->estActif() ? 'Actif' : 'Inactif' ?></span>
                        </div>
                    </div>
                    <div class="employe-catalogue-item__meta">
                        <span><?= $menu->getNombrePlats() ?> plat(s)</span>
                        <span>Stock : <?= $menu->getStockDisponible() ?></span>
                        <span class="commande-item__prix"><?= number_format($menu->getPrixParPersonne(), 2, ',', ' ') ?> € / pers.</span>
                    </div>
                </div>

                <div class="commande-item__actions">
                    <button type="button" class="btn-compte-action btn-compte-action--modifier" data-bascule="form-menu-<?= $id ?>" aria-expanded="false" aria-controls="form-menu-<?= $id ?>">
                        Modifier<span class="visually-hidden"> le menu <?= htmlspecialchars($menu->getTitre()) ?></span>
                    </button>
                    <form method="POST" action="<?= $page ?>" class="d-inline" data-confirmation="Supprimer définitivement le menu « <?= htmlspecialchars($menu->getTitre()) ?> » ?">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="action" value="delete_menu">
                        <input type="hidden" name="menu_id" value="<?= $id ?>">
                        <button type="submit" class="btn-compte-action btn-compte-action--annuler">
                            Supprimer<span class="visually-hidden"> le menu <?= htmlspecialchars($menu->getTitre()) ?></span>
                        </button>
                    </form>
                </div>

                <div class="commande-modif-form" id="form-menu-<?= $id ?>" hidden>
                    <?php if (!empty($menu->getImages())): ?>
                        <p class="commande-label">Photos actuelles</p>
                        <div class="galerie-admin">
                            <?php foreach ($menu->getImages() as $image): ?>
                                <form method="POST" action="<?= $page ?>" class="galerie-admin__item" data-confirmation="Supprimer cette photo ?">
                                    <?= Csrf::champ() ?>
                                    <input type="hidden" name="action" value="delete_menu_image">
                                    <input type="hidden" name="image_id" value="<?= $image->getId() ?>">
                                    <img src="<?= $rootPath . htmlspecialchars($image->getUrl()) ?>" alt="">
                                    <button type="submit" class="galerie-admin__supprimer" aria-label="Supprimer la photo <?= $image->getOrdre() ?>">✕</button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php View::partial('gestion/formulaire-menu', [...$formulaire, 'menu' => $menu]); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PLATS -->
<div class="employe-sous-panel" id="sous-plats" role="tabpanel" aria-labelledby="sous-onglet-plats">
    <div class="employe-catalogue-header">
        <h2 class="employe-section-titre">Gestion des plats</h2>
        <button type="button" class="btn-compte-action btn-compte-action--modifier" data-bascule="form-nouveau-plat" aria-expanded="false" aria-controls="form-nouveau-plat">+ Nouveau plat</button>
    </div>

    <div class="admin-search-bar" role="search" aria-label="Filtrer les plats">
        <label for="filtre-plat-nom" class="visually-hidden">Rechercher un plat</label>
        <input type="search" id="filtre-plat-nom" class="commande-input" placeholder="Rechercher un plat...">
        <label for="filtre-plat-menu" class="visually-hidden">Filtrer les plats par menu</label>
        <select id="filtre-plat-menu" class="commande-input">
            <option value="">Tous les menus</option>
            <?php foreach ($menus as $menu): ?>
                <option value="<?= $menu->getId() ?>"><?= htmlspecialchars($menu->getTitre()) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="commande-modif-form" id="form-nouveau-plat" hidden>
        <?php View::partial('gestion/formulaire-plat', [...$formulaire, 'plat' => null]); ?>
    </div>

    <div class="employe-catalogue" id="liste-plats">
        <?php foreach ($plats as $plat): $id = $plat->getId(); ?>
            <div class="employe-catalogue-item js-plat <?= $plat->estActif() ? '' : 'item--inactif' ?>"
                 data-nom="<?= htmlspecialchars(mb_strtolower($plat->getTitre())) ?>"
                 data-menus=",<?= implode(',', $plat->getMenuIds()) ?>,">
                <div class="employe-catalogue-item__header">
                    <div>
                        <h3 class="commande-item__num"><?= htmlspecialchars($plat->getTitre()) ?></h3>
                        <span class="commande-statut <?= $plat->estActif() ? 'statut--accepte' : 'statut--annule' ?>"><?= $plat->estActif() ? 'Actif' : 'Inactif' ?></span>
                        <span class="commande-statut statut--prep"><?= htmlspecialchars($plat->getCategorie()) ?></span>
                        <p class="mb-0 mt-1"><small>Menus : <strong><?= htmlspecialchars($plat->getTitresMenus() ?: 'aucun') ?></strong></small></p>
                        <?php if (!empty($plat->getAllergenes())): ?>
                            <p class="plat-item__allergenes mb-0">Allergènes : <?= htmlspecialchars($plat->getAllergenesTexte()) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="commande-item__actions">
                    <button type="button" class="btn-compte-action btn-compte-action--modifier" data-bascule="form-plat-<?= $id ?>" aria-expanded="false" aria-controls="form-plat-<?= $id ?>">
                        Modifier<span class="visually-hidden"> le plat <?= htmlspecialchars($plat->getTitre()) ?></span>
                    </button>
                    <form method="POST" action="<?= $page ?>" class="d-inline" data-confirmation="Supprimer le plat « <?= htmlspecialchars($plat->getTitre()) ?> » de tous les menus ?">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="action" value="delete_plat">
                        <input type="hidden" name="plat_id" value="<?= $id ?>">
                        <button type="submit" class="btn-compte-action btn-compte-action--annuler">
                            Supprimer<span class="visually-hidden"> le plat <?= htmlspecialchars($plat->getTitre()) ?></span>
                        </button>
                    </form>
                </div>

                <div class="commande-modif-form" id="form-plat-<?= $id ?>" hidden>
                    <?php View::partial('gestion/formulaire-plat', [...$formulaire, 'plat' => $plat]); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="compte-empty" id="aucun-plat" hidden>
        <h3>Aucun plat trouvé</h3>
        <p>Modifiez vos filtres pour voir d'autres plats.</p>
    </div>
</div>
