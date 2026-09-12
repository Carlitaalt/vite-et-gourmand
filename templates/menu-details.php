<?php
/**
 * @var App\Entity\Menu $menu
 * @var bool $estConnecte
 */
$imagePrincipale = $rootPath . ($menu->getImagePrincipale() ?? 'assets/images/menu-prestige.jpg');
?>

<section class="detail-hero">
    <div class="detail-hero__bg" id="detail-hero-image" style="background-image: url('<?= htmlspecialchars($imagePrincipale) ?>');"></div>
    <div class="detail-hero__overlay"></div>
    <div class="container detail-hero__content">
        <a href="<?= $rootPath ?>pages/menus.php" class="detail-back">← Retour aux menus</a>
        <div class="detail-hero__badges">
            <span class="badge-theme badge-theme--<?= $menu->getTheme()->getId() ?>"><?= htmlspecialchars($menu->getTheme()->getLibelle()) ?></span>
            <span class="regime-pill regime--<?= $menu->getRegime()->getSlug() ?>"><?= htmlspecialchars($menu->getRegime()->getLibelle()) ?></span>
        </div>
        <h1 class="detail-hero__title"><?= htmlspecialchars($menu->getTitre()) ?></h1>
        <div class="detail-hero__meta">
            <span class="detail-meta-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                À partir de <strong><?= $menu->getNombrePersonneMinimum() ?> personnes</strong>
            </span>
        </div>
    </div>
</section>

<section class="section-detail">
    <div class="container">
        <div class="detail-layout">

            <div class="detail-main">

                <!-- Conditions en premier et bien visibles (exigence du cahier des charges) -->
                <?php if ($menu->getConditions() !== ''): ?>
                    <div class="detail-block detail-conditions-alerte" role="note" aria-labelledby="titre-conditions">
                        <h2 class="detail-block__title" id="titre-conditions">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                            Conditions de ce menu — à lire avant de commander
                        </h2>
                        <p><?= nl2br(htmlspecialchars($menu->getConditions())) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (count($menu->getImages()) > 1): ?>
                    <div class="detail-block">
                        <h2 class="detail-block__title">Galerie</h2>
                        <div class="detail-gallery">
                            <?php foreach ($menu->getImages() as $index => $image): ?>
                                <button type="button" class="detail-gallery__vignette" data-image="<?= $rootPath . htmlspecialchars($image->getUrl()) ?>" aria-label="Afficher la photo <?= $index + 1 ?> en grand">
                                    <img src="<?= $rootPath . htmlspecialchars($image->getUrl()) ?>" alt="">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="detail-block">
                    <h2 class="detail-block__title">À propos de ce menu</h2>
                    <p class="detail-desc"><?= nl2br(htmlspecialchars($menu->getDescription())) ?></p>
                </div>

                <?php if (!empty($menu->getPlatsParCategorie())): ?>
                    <div class="detail-block">
                        <h2 class="detail-block__title">Composition du menu</h2>
                        <div class="detail-plats">
                            <?php foreach ($menu->getPlatsParCategorie() as $categorie => $plats): ?>
                                <div class="plats-categorie">
                                    <h3 class="plats-categorie__titre"><?= htmlspecialchars($categorie) ?></h3>
                                    <ul class="plats-liste">
                                        <?php foreach ($plats as $plat): ?>
                                            <li class="plat-item">
                                                <div class="plat-item__nom"><?= htmlspecialchars($plat->getTitre()) ?></div>
                                                <?php if ($plat->getDescription() !== ''): ?>
                                                    <div class="plat-item__desc"><?= htmlspecialchars($plat->getDescription()) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($plat->getAllergenes())): ?>
                                                    <div class="plat-item__allergenes">
                                                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                                        Allergènes : <?= htmlspecialchars($plat->getAllergenesTexte()) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="detail-sidebar" aria-label="Prix et commande">
                <div class="detail-card-prix">
                    <div class="detail-card-prix__header">
                        <span class="detail-card-prix__label">Prix pour <?= $menu->getNombrePersonneMinimum() ?> personnes</span>
                        <div class="detail-card-prix__montant"><?= number_format($menu->getPrixMinimum(), 2, ',', ' ') ?> €</div>
                        <div class="detail-card-prix__base">soit <?= number_format($menu->getPrixParPersonne(), 2, ',', ' ') ?> € par personne</div>
                    </div>
                    <dl class="detail-card-prix__body">
                        <div class="detail-card-info">
                            <dt class="detail-card-info__label">Thème</dt>
                            <dd class="detail-card-info__val"><?= htmlspecialchars($menu->getTheme()->getLibelle()) ?></dd>
                        </div>
                        <div class="detail-card-info">
                            <dt class="detail-card-info__label">Régime</dt>
                            <dd class="detail-card-info__val"><?= htmlspecialchars($menu->getRegime()->getLibelle()) ?></dd>
                        </div>
                        <div class="detail-card-info">
                            <dt class="detail-card-info__label">Personnes min.</dt>
                            <dd class="detail-card-info__val"><?= $menu->getNombrePersonneMinimum() ?> pers.</dd>
                        </div>
                        <div class="detail-card-info">
                            <dt class="detail-card-info__label">Disponibilité</dt>
                            <dd class="detail-card-info__val"><?= $menu->getStockDisponible() ?> commande(s) restante(s)</dd>
                        </div>
                    </dl>
                    <div class="detail-card-prix__footer">
                        <?php if (!$menu->estDisponible()): ?>
                            <button class="btn btn-vg-primary w-100" disabled>Complet</button>
                        <?php elseif ($estConnecte): ?>
                            <a href="<?= $rootPath ?>pages/commande.php?menu=<?= $menu->getId() ?>" class="btn btn-vg-primary w-100">Commander ce menu →</a>
                        <?php else: ?>
                            <!-- Visiteur : connexion ou création de compte obligatoire avant la commande -->
                            <p class="detail-card-connexion">Connectez-vous ou créez un compte pour commander ce menu.</p>
                            <a href="<?= $rootPath ?>pages/commande.php?menu=<?= $menu->getId() ?>" class="btn btn-vg-primary w-100">Se connecter pour commander</a>
                            <a href="<?= $rootPath ?>pages/inscription.php" class="btn btn-vg-secondary w-100 mt-2">Créer un compte</a>
                        <?php endif; ?>
                        <a href="<?= $rootPath ?>pages/menus.php" class="btn btn-vg-secondary w-100 mt-2">← Voir tous les menus</a>
                    </div>
                </div>

                <div class="detail-card-custom">
                    <h2>Menu sur mesure ?</h2>
                    <p>Nous adaptons chaque formule à vos envies et contraintes alimentaires.</p>
                    <a href="<?= $rootPath ?>pages/contact.php" class="btn-or">Nous contacter</a>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
    // Galerie : cliquer sur une vignette affiche la photo en grand dans l'en-tête
    document.querySelectorAll('.detail-gallery__vignette').forEach(bouton => {
        bouton.addEventListener('click', () => {
            document.getElementById('detail-hero-image').style.backgroundImage = `url('${bouton.dataset.image}')`;
        });
    });
</script>
