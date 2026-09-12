<?php
/**
 * Carte d'un menu dans la vue globale.
 * La même structure HTML est générée en JavaScript (assets/js/menus.js) après un appel fetch.
 *
 * @var App\Entity\Menu $menu
 */
$stock = $menu->getStockDisponible();
?>
<article class="menu-card">
    <div class="menu-card__img">
        <?php if ($menu->getImagePrincipale()): ?>
            <img src="<?= $rootPath . htmlspecialchars($menu->getImagePrincipale()) ?>" alt="Photo du menu <?= htmlspecialchars($menu->getTitre()) ?>">
        <?php else: ?>
            <div class="menu-card__img-placeholder">Aucune image</div>
        <?php endif; ?>
        <span class="badge-theme badge-theme--<?= $menu->getTheme()->getId() ?>"><?= htmlspecialchars($menu->getTheme()->getLibelle()) ?></span>
    </div>

    <div class="menu-card__body">
        <div class="menu-card__pills">
            <span class="regime-pill regime--<?= $menu->getRegime()->getSlug() ?>"><?= htmlspecialchars($menu->getRegime()->getLibelle()) ?></span>
        </div>

        <h2 class="menu-card__titre"><?= htmlspecialchars($menu->getTitre()) ?></h2>

        <?php if ($menu->getDescription() !== ''): ?>
            <p class="menu-card__desc"><?= htmlspecialchars($menu->getDescription()) ?></p>
        <?php endif; ?>

        <div class="menu-card__meta">
            <div class="menu-card__personnes">À partir de <strong><?= $menu->getNombrePersonneMinimum() ?> pers.</strong></div>
            <div class="menu-card__prix">
                <?= number_format($menu->getPrixMinimum(), 2, ',', ' ') ?> €
                <small><?= number_format($menu->getPrixParPersonne(), 2, ',', ' ') ?> € / pers.</small>
            </div>
        </div>

        <?php if ($stock <= 0): ?>
            <p class="menu-card__stock menu-card__stock--complet">Complet — plus de disponibilité</p>
        <?php elseif ($stock <= 3): ?>
            <p class="menu-card__stock menu-card__stock--faible">Plus que <?= $stock ?> commande(s) possible(s) !</p>
        <?php else: ?>
            <p class="menu-card__stock menu-card__stock--ok">Disponible — <?= $stock ?> commandes possibles</p>
        <?php endif; ?>
    </div>

    <div class="menu-card__footer">
        <a href="<?= $rootPath ?>pages/menu-details.php?id=<?= $menu->getId() ?>" class="btn btn-vg-primary w-100">
            Voir le détail<span class="visually-hidden"> du menu <?= htmlspecialchars($menu->getTitre()) ?></span> →
        </a>
    </div>
</article>
