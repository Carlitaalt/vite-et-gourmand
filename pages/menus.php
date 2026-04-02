<?php
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
        (m.prix_par_personne * m.nombre_personne_minimum) AS prix_total,
        t.nom AS theme_nom,
        r.nom AS regime_nom
        FROM menus m
        LEFT JOIN theme t on t.theme_id = m.theme_id
        ORDER BY m.menu_id ASC
        ");
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        //Silencieux en production
    }
}

if(empty($menus)){
    $menus = [
        [
            'menu_id' => 1,
            'titre' => 'Le grand Festion de Noël',
            'description' => 'Un menu somptueux pour célébrer les fêtes en grande pompe. Dinde rôtie aux marrons, bûche maison et vin chaud d\'épices.',
            'conditions' => 'À commander 7 jours avant la prestation.',
            'nombre_personne_minimum' => 10,
            'prix_par_personne' => 32.00,
            'prix_total' => 320.00,
            'theme_nom' => 'Noël',
            'regime_nom' => 'Classique'
        ],
        [
            'menu_id' => 2,
            'titre' => 'Printemps & Pâques',
            'description' => 'Menu végétarien aux saveurs printanières : velouté d\'asperges, tarte aux légumes du jardin et dessert aux fruits rouges.',
            'conditions' => 'À commander 5jours avant la prestation.',
            'nombre_personne_minimum' => 8,
            'prix_par_personne' => 22.50,
            'prix_total' => 180,
            'theme_nom' => 'Pâques',
            'regime_nom' => 'Végétarien'
        ],
        [
            'menu_id' => 3,
            'titre' => 'Menu prestige Classique',
            'description' => 'L\'excellence de la gastronomie française : foie gras mi-cuit, filet de boeuf Wellington et soufflé au Grand Marnier.',
            'conditions' => 'À commander 10 jours avant la prestation.',
            'nombre_personne_minimum' => 20,
            'prix_par_personne' => 22.50,
            'prix_total' => 450.00,
            'theme_nom' => 'Prestige',
            'regime_nom' => 'Classique'
        ],
        [
            'menu_id' => 4,
            'titre' => 'Coktail Dinatoire Végan',
            'description' => 'Une expérience culinaire 100% végétale, colorée et conviviale. Tapas créatifs, bouchées gourmandes et desserts raffinés.',
            'conditions' => 'À commander 5 jours avant la prestation.',
            'nombre_personne_minimum' => 15,
            'prix_par_personne' => 14.00,
            'prix_total' => 210.00,
            'theme_nom' => 'Végan',
            'regime_nom' => 'Végan'
        ]
    ];
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
                    <img src="<?= $rootPath ?>assets/images/menu-<?= $themeSlug ?>.jpg" alt="<?= htmlspecialchars($menu['titre']) ?>" class="menu-img">
                    <div class="menu-card__img-placeholder"></div>
                        <span class="badge-theme <?= $badgeClass ?>">
                        <?= htmlspecialchars($menu['theme_nom'] ?? 'Classique') ?>
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
                </div>

                <!-- Footer -->
                <div class="menu-card__footer">
                    <a href="<?= $rootPath ?>pages/menu-detail.php?id=<?= (int)$menu['menu_id'] ?>" class="btn btn-vg-primary w-100">
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
                <a href="<?= $rootPath ?>contact.php" class="btn-or">Demander un devis</a>
            </div>
        </div>
    </div>
 </section>


<?php require_once '../includes/footer.php'; ?>