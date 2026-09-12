<?php
use App\Core\View;

/**
 * @var App\Entity\Menu[] $menus
 * @var App\Entity\Theme[] $themes
 * @var App\Entity\Regime[] $regimes
 */
$filtre = fn(string $cle): string => htmlspecialchars((string) ($filtres[$cle] ?? ''));
$prixMaxCurseur = is_numeric($filtres['prix_max'] ?? null) ? (int) $filtres['prix_max'] : 1000;
?>

<section class="section-menus">
    <div class="container">

        <div class="text-center mb-5">
            <span class="section-label">Notre carte</span>
            <h1 class="section-title">Choisissez votre formule</h1>
            <div class="divider-or mx-auto"></div>
        </div>

        <!-- Filtres : sans JavaScript le formulaire est envoyé en GET, avec JavaScript la liste est actualisée par fetch -->
        <form class="menus-filters" id="filtres-menus" method="get" action="menus.php" data-racine="<?= $rootPath ?>" role="search" aria-label="Filtrer les menus">
            <div class="menus-filters__head">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M3 5h14M6 10h8M9 15h2" stroke-linecap="round" />
                </svg>
                <span>Affiner la recherche</span>
            </div>

            <div class="menus-filters__grid">
                <div class="filter-group">
                    <label for="f-price-max">Prix maximum</label>
                    <input type="range" id="f-price-max" name="prix_max" min="0" max="1000" step="10" value="<?= $prixMaxCurseur ?>" aria-describedby="f-price-max-val">
                    <div class="filter-range-readout">
                        <span>0 €</span>
                        <strong class="f-price-max-val" id="f-price-max-val"><?= $prixMaxCurseur ?> €</strong>
                    </div>
                </div>

                <fieldset class="filter-group">
                    <legend class="filter-legend">Fourchette de prix</legend>
                    <div class="filter-range-row">
                        <input type="number" id="f-pmin" name="prix_min" placeholder="Min €" min="0" value="<?= $filtre('prix_min') ?>" aria-label="Prix minimum en euros">
                        <span class="filter-sep" aria-hidden="true">-</span>
                        <input type="number" id="f-pmax" name="prix_plafond" placeholder="Max €" min="0" value="<?= $filtre('prix_plafond') ?>" aria-label="Prix maximum en euros">
                    </div>
                </fieldset>

                <div class="filter-group">
                    <label for="f-theme">Thème</label>
                    <select id="f-theme" name="theme">
                        <option value="">Tous les thèmes</option>
                        <?php foreach ($themes as $theme): ?>
                            <option value="<?= $theme->getId() ?>" <?= $filtre('theme') === (string) $theme->getId() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($theme->getLibelle()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="f-regime">Régime</label>
                    <select id="f-regime" name="regime">
                        <option value="">Tous les régimes</option>
                        <?php foreach ($regimes as $regime): ?>
                            <option value="<?= $regime->getId() ?>" <?= $filtre('regime') === (string) $regime->getId() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($regime->getLibelle()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="f-persons">Nombre de personnes minimum</label>
                    <input type="number" id="f-persons" name="personnes" placeholder="ex : 10" min="1" value="<?= $filtre('personnes') ?>">
                </div>
            </div>

            <div class="menus-filters__footer">
                <noscript><button class="btn btn-vg-primary" type="submit">Filtrer</button></noscript>
                <button class="btn-reset" id="btn-reset" type="button">↺ Réinitialiser</button>
            </div>
        </form>

        <!-- Nombre de résultats, annoncé aux lecteurs d'écran à chaque actualisation -->
        <div class="menus-results-bar">
            <p class="menus-results-count" aria-live="polite">
                <strong class="results-num"><?= count($menus) ?></strong> menu(s) disponible(s)
            </p>
        </div>

        <div class="menus-grid" id="menus-grid" <?= empty($menus) ? 'hidden' : '' ?>>
            <?php foreach ($menus as $menu): ?>
                <?php View::partial('partials/carte-menu', ['menu' => $menu, 'rootPath' => $rootPath]); ?>
            <?php endforeach; ?>
        </div>

        <div class="menus-empty" id="menus-empty" <?= empty($menus) ? '' : 'hidden' ?>>
            <div class="menu-empty__icon"></div>
            <h2>Aucun menu ne correspond</h2>
            <p>Essayez d'élargir vos critères de recherche.</p>
            <button class="btn btn-vg-primary mt-3" id="btn-reset-empty" type="button">Réinitialiser les filtres</button>
        </div>

    </div>
</section>

<section class="section-cta py-5">
    <div class="container">
        <div class="cta-box">
            <div class="cta-deco"></div>
            <h2 class="cta-title">Un menu sur-mesure ?</h2>
            <p class="cta-desc">Contactez-nous pour adapter l'une de nos formules à vos besoins ou créer un menu entièrement personnalisé.</p>
            <div class="cta-actions">
                <a href="<?= $rootPath ?>pages/contact.php" class="btn-or">Nous contacter</a>
            </div>
        </div>
    </div>
</section>
