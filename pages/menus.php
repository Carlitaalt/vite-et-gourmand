<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

$pageTitle = 'Nos Menus';
$rootPath = '../';
$currentPage = 'menus';


require_once '../includes/header.php';
require_once '../includes/navbar.php';

$menus = [];
if(isset($pdo)){
    try {
        $stmt = $pdo->query("
        SELECT
        m.menu_id,
        m.titre,
        m.description,
        m.conditions,
        m.nombre_personne_minimum,
        m.prix_par_personne,
        m.stock_disponible,
        (m.prix_par_personne * m.nombre_personne_minimum) AS prix_total,
        mi.url AS image_url,
        t.libelle AS theme_nom,
        r.libelle AS regime_nom
        FROM menu m
        LEFT JOIN menu_image mi ON m.menu_id = mi.menu_id AND mi.ordre = 1
        LEFT JOIN theme t ON m.theme_id = t.theme_id
        LEFT JOIN regime r ON m.regime_id = r.regime_id
        WHERE m.actif = 1
        ORDER BY m.menu_id ASC
        ");
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Ce message s'affichera seulement s'il y a une erreur réelle (ex: faute de frappe)
        echo "<div style='background:white; color:red; padding:20px; border:2px solid red;'>";
        echo "Erreur SQL : " . $e->getMessage();
        echo "</div>";
    }
}


function toSlug(string $str): string {
    $str = mb_strtolower(trim($str), 'UTF-8');
    $map = ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae'];
    $str = strtr($str, $map);
    return preg_replace('/[^a-z0-9]+/', '-', $str);
}

$themeBadgeClass = [
    'noel' => 'badge-theme--noel',
    'paques' => 'badge-theme--paques',
    'classique' => 'badge-theme--classique',
    'evenement' => 'badge-theme--evenement',
    'ete' => 'badge-theme--evenement'
];

$regimePillClass = [
    'classique' => 'regime--classique',
    'vegetarien' => 'regime--vegetarien',
    'vegan' => 'regime--vegan'
];

?>

<!-- HERO -->

<section class="section-menus">
    <div class="container">

    <div class="text-center mb-5">
        <span class="section-label">Notre carte</span>
        <h2 class="section-title">Choisissez votre formule</h2>
        <div class="divider-or mx-auto"></div>
    </div>

    <!-- Filtres -->
     <div class="menus-filters">
        <div class="menus-filters__head">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M3 5h14M6 10h8M9 15h2" stroke-linecap="round" />
            </svg>
            <span>Affiner la recherche</span>
        </div>

        <div class="menus-filters__grid">
            <div class="filter-group">
                <label for="f-price-max">Prix maximum</label>
                <input type="range" id="f-price-max" min="0" max="1000" value="1000" step="10">
                <div class="filter-range-readout">
                    <span>0 €</span>
                    <strong class="f-price-max-val">1000 €</strong>
                </div>
            </div>

            <div class="filter-group">
                <label for="f-pmin">Fourchette de prix</label>
                <div class="filter-range-row">
                    <input type="number" id="f-pmin" placeholder="Min €" min="0" aria-label="Prix minimum">
                    <span class="filter-sep">-</span>
                    <input type="number" id="f-pmax" placeholder="Max €" min="0" aria-label="Prix maximum">
                </div>
            </div>

            <div class="filter-group">
                <label for="f-theme">Thème</label>
                <select id="f-theme">
                    <option value="">Tous les thèmes</option>
                    <?php
                    $themesVus = [];
                    foreach ($menus as $m) {
                        $slug = toSlug($m['theme_nom'] ?? '');
                        if($slug && !in_array($slug, $themesVus)){
                            $themesVus[] = $slug;
                            echo '<option value="' . htmlspecialchars($slug) . '">' . htmlspecialchars($m['theme_nom']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="f-regime">Régime</label>
                <select id="f-regime">
                    <option value="">Tous les régimes</option>
                    <?php
                    $regimesVus = [];
                    foreach ($menus as $m) {
                        $slug = toSlug($m['regime_nom'] ?? '');
                        if($slug && !in_array($slug, $regimesVus)) {
                            $regimesVus[] = $slug;
                            echo '<option value="' . htmlspecialchars($slug) . '">' . htmlspecialchars($m['regime_nom']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="f-persons">Personnes minimum</label>
                <input type="number" id="f-persons" placeholder="ex : 10" min="1">
            </div>

        </div>
        
        <div class="menus-filters__footer">
            <button class="btn-reset" id="btn-reset" type="button">↺ Réinitialiser</button>
        </div>
     </div>

     <!-- Barre résultats -->
      <div class="menus-results-bar">
        <p class="menus-results-count">
            <strong class="results-num"><?= count($menus) ?></strong>
            menus disponibles
        </p>
      </div>

      <!-- Grille -->
        <div class="menus-grid" id="menus-grid">
            <?php foreach ($menus as $menu):
                $themeSlug = toSlug($menu['theme_nom'] ?? 'classique');
                $regimeSlug = toSlug($menu['regime_nom'] ?? 'classique');
                $prixTotal = (float)($menu['prix_total'] ?? ($menu['prix_par_personne'] * $menu['nombre_personne_minimum']));
                $badgeClass = $themeBadgeClass[$themeSlug] ?? 'badge-theme--classique';
                $pillClass = $regimePillClass[$regimeSlug] ?? 'regime--classique';
            ?>
            <article class="menu-card"
                data-theme="<?= htmlspecialchars($themeSlug) ?>"
                data-regime="<?= htmlspecialchars(($regimeSlug)) ?>"
                data-prix="<?= (int)$prixTotal ?>"
            >

                <!-- Visuel -->
                <div class="menu-card__img">
                    <?php if (!empty($menu['image_url'])): ?>
                        <img src="../<?= htmlspecialchars($menu['image_url']) ?>" alt="<?= htmlspecialchars($menu['titre']) ?>">
                    <?php else: ?>
                        <div class="menu-card__img-placeholder">Aucune image</div>
                    <?php endif; ?>
                    <span class="badge-theme <?= $badgeClass ?>">
                        <?=  htmlspecialchars($menu['theme_nom'] ?? 'Classique') ?>
                    </span>
                </div>

                <!-- Corps -->
                <div class="menu-card__body">
                    <div class="menu-card__pills">
                        <span class="regime-pill <?= $pillClass ?>">
                            <?= htmlspecialchars($menu['regime_nom'] ?? 'Classique') ?>
                        </span>
                    </div>

                    <h2 class="menu-card__titre"><?= htmlspecialchars($menu['titre']) ?></h2>

                    <?php if (!empty($menu['description'])) : ?>
                        <p class="menu-card__desc"><?= htmlspecialchars($menu['description']) ?></p>
                    <?php endif; ?>

                    <div class="menu-card__meta">
                        <div class="menu-card__personnes">
                            À partir de <strong><?= (int)$menu['nombre_personne_minimum'] ?> pers.</strong>
                        </div>
                        <div class="menu-card__prix">
                            <?= number_format($prixTotal, 2, ',', ' ') ?> €
                            <small><?= number_format((float)$menu['prix_par_personne'], 2, ',', ' ') ?> / pers.</small>
                        </div>
                    </div>

                    <?php if ((int)$menu['stock_disponible'] <= 0): ?>
                        <p class="menu-card__stock" style="color:#c0392b; font-weight:600; margin-top:8px;">Complet — plus de disponibilité</p>
                    <?php elseif ((int)$menu['stock_disponible'] <= 3): ?>
                        <p class="menu-card__stock" style="color:#e67e22; font-weight:600; margin-top:8px;">Plus que <?= (int)$menu['stock_disponible'] ?> commande(s) possible(s) !</p>
                    <?php else: ?>
                        <p class="menu-card__stock" style="color:#27ae60; margin-top:8px;">Disponible — <?= (int)$menu['stock_disponible'] ?> commandes possibles</p>
                    <?php endif; ?>
                </div>


                <!-- Footer -->
                <div class="menu-card__footer">
                    <a href="<?= $rootPath ?>pages/menu-details.php?id=<?= (int)$menu['menu_id'] ?>" class="btn btn-vg-primary w-100">
                        Voir détails →
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- État vide -->
         <div class="menus-empty" id="menus-empty" style="display: none;" aria-live="polite">
            <div class="menu-empty__icon"></div>
            <h3>Aucun menu ne correspond</h3>
            <p>Essayez d'élargir vos critères de recherche.</p>
            <button class="btn btn-vg-primary mt-3" id="btn-reset-empty" type="button">Réintialiser les filtres</button>
         </div>


    </div>
</section>

<!-- CTA -->
 <section class="section-cta py-5">
    <div class="container">
        <div class="cta-box">
            <div class="cta-deco"></div>
            <h2 class="cta-title">Un menu sur-mesure ?</h2>
            <p class="cta-desc">Contactez-nous pour adapter l'une de nos formules à vos besoins ou créer un menu entièrement personnalisé.</p>
            <div class="cta-actions">
                <a href="<?= $rootPath ?>pages/contact.php" class="btn-or">Demander un devis</a>
            </div>
        </div>
    </div>
 </section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fPriceMaxRange = document.getElementById('f-price-max');
        const fPriceMaxVal = document.querySelector('.f-price-max-val');
        const fPriceMinInput = document.getElementById('f-pmin');
        const fPriceMaxInput = document.getElementById('f-pmax');
        const fTheme = document.getElementById('f-theme');
        const fRegime = document.getElementById('f-regime');
        const fPersons = document.getElementById('f-persons');

        const btnReset = document.getElementById('btn-reset');
        const btnResetEmpty = document.getElementById('btn-reset-empty');

        const menuGrid = document.getElementById('menus-grid');
        const menuCards = document.querySelectorAll('.menu-card');
        const menuEmpty = document.getElementById('menus-empty');
        const resultsNum = document.querySelector('.results-num');

        function filterMenus() {
            let visibleCount = 0;

            //Valeurs dans les filtres
            const priceMaxRange = parseFloat(fPriceMaxRange.value);
            const priceMin = parseFloat(fPriceMinInput.value) || 0;
            const priceMax = parseFloat(fPriceMaxInput.value) || Infinity;
            const theme = fTheme.value;
            const regime = fRegime.value;
            const personsMinNeeded = parseInt(fPersons.value) || 0;

            //Mise à jour de l'affichage du prix au-dessus du range
            if (fPriceMaxVal) fPriceMaxVal.textContent = priceMaxRange + ' €';

            menuCards.forEach(card => {
                const cardPrice = parseFloat(card.dataset.prix);
                const cardTheme = card.dataset.theme;
                const cardRegime = card.dataset.regime;
                //On récupère le nombre de personnes min via le texte
                const cardPersons = parseInt(card.querySelector('.menu-card__personnes strong').textContent);

                const matchPriceRange = cardPrice <= priceMaxRange;
                const matchPriceManual = cardPrice >= priceMin && cardPrice <= priceMax;
                const matchTheme = theme === "" || cardTheme === theme;
                const matchRegime = regime === "" || cardRegime === regime;
                const matchPersons = cardPersons >= personsMinNeeded;

                if(matchPriceRange && matchPriceManual && matchTheme && matchRegime && matchPersons) {
                    card.style.display = 'flex';
                    visibleCount ++;
                } else {
                    card.style.display = 'none';
                }
            });

            //Gestion de l'état vide
            resultsNum.textContent = visibleCount;
            if(visibleCount === 0) {
                menuGrid.style.display = 'none';
                menuEmpty.style.display = 'block';
            } else {
                menuGrid.style.display = 'grid';
                menuEmpty.style.display = 'none';
            }
        }

        function resetFilters() {
            fPriceMaxRange.value = 1000;
            fPriceMinInput.value = '';
            fPriceMaxInput.value = '';
            fTheme.value = '';
            fRegime.value = '';
            fPersons.value = '';
            filterMenus();
        }
        
        //Écouteurs d'évènements
        [fPriceMaxRange, fPriceMinInput, fPriceMaxInput, fTheme, fRegime, fPersons].forEach(el => {
            el.addEventListener('input', filterMenus);
        });

        btnReset.addEventListener('click', resetFilters);
        btnResetEmpty.addEventListener('click', resetFilters);
    });

</script>


<?php require_once '../includes/footer.php'; ?>