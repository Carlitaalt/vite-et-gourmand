<?php
$pageTitle = 'Détail du Menu';
$rootPath = '../';
$currentPage = 'menus';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$menu = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(isset($pdo) && $id > 0) {
    try {
        $stmt = $pdo->prepare("
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
        LEFT JOIN theme t ON t.theme_id = m.theme_id
        LEFT JOIN r ON r.regime_id = m.regime_id
        WHERE m.menu_id = :id
        ");
        $stmt->execute([':id' => $id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e){
        //Silencieux en production
    }
}

// Données fictives si pas de BDD ou menu introuvable
if (!$menu) {
    $menus_fictifs = [
        1 => [
            'menu_id'                  => 1,
            'titre'                    => 'Le Grand Festin de Noël',
            'description'              => 'Un menu somptueux pour célébrer les fêtes en grande pompe. Dinde rôtie aux marrons, bûche maison et vin chaud d\'épices. Chaque plat est préparé avec des produits frais et locaux, sélectionnés avec soin par notre chef.',
            'conditions'               => 'À commander 7 jours avant la prestation. Livraison incluse dans un rayon de 30 km. Vaisselle et service en option.',
            'nombre_personne_minimum'  => 10,
            'prix_par_personne'        => 32.00,
            'prix_total'               => 320.00,
            'theme_nom'                => 'Noël',
            'regime_nom'               => 'Classique',
            'image'                    => 'menu-noel.jpg',
            'plats' => [
                ['categorie' => 'Entrée',   'nom' => 'Velouté de châtaignes & crème de truffe',      'desc' => 'Doux et parfumé, servi avec des croûtons maison.'],
                ['categorie' => 'Entrée',   'nom' => 'Saumon gravlax aux agrumes',                    'desc' => 'Mariné 48h, accompagné de sa sauce moutarde à l\'aneth.'],
                ['categorie' => 'Plat',     'nom' => 'Dinde rôtie aux marrons & jus de volaille',    'desc' => 'Cuite lentement au four, farce traditionnelle aux herbes.'],
                ['categorie' => 'Plat',     'nom' => 'Gratin dauphinois & haricots verts amandine',  'desc' => 'Accompagnements généreux et fondants.'],
                ['categorie' => 'Fromage',  'nom' => 'Plateau de fromages affinés',                   'desc' => 'Sélection de 5 fromages de producteurs locaux.'],
                ['categorie' => 'Dessert',  'nom' => 'Bûche de Noël maison au praliné',              'desc' => 'Biscuit moelleux, ganache praliné noisette.'],
                ['categorie' => 'Boisson',  'nom' => 'Vin chaud aux épices & jus de pomme chaud',    'desc' => 'Servi à l\'arrivée de vos convives.'],
            ]
        ],
        2 => [
            'menu_id'                  => 2,
            'titre'                    => 'Printemps & Pâques',
            'description'              => 'Menu végétarien aux saveurs printanières : velouté d\'asperges, tarte aux légumes du jardin et dessert aux fruits rouges. Un repas léger et coloré qui célèbre le retour des beaux jours.',
            'conditions'               => 'À commander 5 jours avant la prestation. Produits de saison garantis.',
            'nombre_personne_minimum'  => 8,
            'prix_par_personne'        => 22.50,
            'prix_total'               => 180.00,
            'theme_nom'                => 'Pâques',
            'regime_nom'               => 'Végétarien',
            'image'                    => 'menu-paques.jpg',
            'plats' => [
                ['categorie' => 'Entrée',  'nom' => 'Velouté d\'asperges vertes & huile de basilic', 'desc' => 'Léger et parfumé, avec des pointes d\'asperge rôties.'],
                ['categorie' => 'Entrée',  'nom' => 'Œufs mimosa aux herbes fraîches',               'desc' => 'Version printanière du classique, garni de ciboulette et estragon.'],
                ['categorie' => 'Plat',    'nom' => 'Tarte fine aux légumes du jardin',              'desc' => 'Courgettes, tomates cerises, poivrons et ricotta.'],
                ['categorie' => 'Plat',    'nom' => 'Risotto aux petits pois & menthe fraîche',      'desc' => 'Crémeux et parfumé, parsemé de parmesan affiné.'],
                ['categorie' => 'Dessert', 'nom' => 'Pavlova aux fruits rouges de saison',           'desc' => 'Meringue croustillante, chantilly légère, coulis de framboise.'],
            ]
        ],
        3 => [
            'menu_id'                  => 3,
            'titre'                    => 'Menu Prestige Classique',
            'description'              => 'L\'excellence de la gastronomie française : foie gras mi-cuit, filet de bœuf Wellington et soufflé au Grand Marnier. Une expérience culinaire d\'exception pour vos événements les plus importants.',
            'conditions'               => 'À commander 10 jours avant la prestation. Service à table disponible en option. Minimum 20 personnes.',
            'nombre_personne_minimum'  => 20,
            'prix_par_personne'        => 22.50,
            'prix_total'               => 450.00,
            'theme_nom'                => 'Classique',
            'regime_nom'               => 'Classique',
            'image'                    => 'menu-prestige.jpg',
            'plats' => [
                ['categorie' => 'Amuse-bouche', 'nom' => 'Verrines de mousse de saumon & caviar',        'desc' => 'Une mise en bouche élégante pour ouvrir les festivités.'],
                ['categorie' => 'Entrée',        'nom' => 'Foie gras mi-cuit, chutney de figues',        'desc' => 'Foie gras de canard, pain brioché toasté maison.'],
                ['categorie' => 'Entrée',        'nom' => 'Carpaccio de Saint-Jacques & truffe',         'desc' => 'Noix de Saint-Jacques crues, huile de truffe noire, roquette.'],
                ['categorie' => 'Plat',          'nom' => 'Filet de bœuf Wellington sauce Périgueux',   'desc' => 'Enrobé de duxelles de champignons et feuilletage doré.'],
                ['categorie' => 'Plat',          'nom' => 'Pommes sarladaises & légumes glacés',        'desc' => 'Pommes de terre confites à l\'ail et au persil.'],
                ['categorie' => 'Fromage',       'nom' => 'Chariot de fromages affinés',                'desc' => 'Large sélection de fromages de France, service à table.'],
                ['categorie' => 'Dessert',       'nom' => 'Soufflé chaud au Grand Marnier',             'desc' => 'Délicat et aérien, servi directement à table.'],
                ['categorie' => 'Mignardises',   'nom' => 'Café & mignardises maison',                  'desc' => 'Chocolats, pâtes de fruit, financiers aux amandes.'],
            ]
        ],
        4 => [
    'menu_id'                 => 4,
    'titre'                   => 'Cocktail Dinatoire Vegan',
    'description'             => 'Une sélection raffinée de bouchées véganes pour épater vos convives. Verrines colorées, mini-burgers végétaux et desserts crus aux fruits exotiques. 100% végétal, 100% gourmand.',
    'conditions'              => 'À commander 5 jours avant la prestation. Livraison incluse dans un rayon de 30 km. Prévoir 8 à 10 pièces par personne.',
    'nombre_personne_minimum' => 15,
    'prix_par_personne'       => 18.00,
    'prix_total'              => 270.00,
    'theme_nom'               => 'Événement',
    'regime_nom'              => 'Vegan',
    'image'                   => 'menu-vegan.jpg',
    'plats' => [
        ['categorie' => 'Bouchées froides', 'nom' => 'Verrines avocat, mangue & grenade',         'desc' => 'Fraîches et colorées, relevées d\'un trait de citron vert.'],
        ['categorie' => 'Bouchées froides', 'nom' => 'Tartare de betterave & noix de cajou',      'desc' => 'Servi sur blinis de sarrasin maison.'],
        ['categorie' => 'Bouchées froides', 'nom' => 'Rouleaux de printemps crudités & tofu',     'desc' => 'Sauce cacahuète légère, coriandre fraîche.'],
        ['categorie' => 'Bouchées chaudes', 'nom' => 'Mini-burgers végétaux & sauce aigre-douce', 'desc' => 'Steak de pois chiches, pickles maison, pain brioché vegan.'],
        ['categorie' => 'Bouchées chaudes', 'nom' => 'Samossas épinards & lentilles corail',      'desc' => 'Dorés au four, servis avec un chutney de mangue.'],
        ['categorie' => 'Bouchées chaudes', 'nom' => 'Brochettes de tofu mariné & légumes grillés','desc' => 'Marinade soja-gingembre, sésame toasté.'],
        ['categorie' => 'Dessert',          'nom' => 'Cheesecake cru aux fruits de la passion',   'desc' => 'Base cajou-dattes, crème coco, coulis passion.'],
        ['categorie' => 'Dessert',          'nom' => 'Truffes chocolat noir & noisette',          'desc' => 'Fondantes et intenses, sans produits animaux.'],
        ]
    ],
    ];
 
    $menu = $menus_fictifs[$id] ?? $menus_fictifs[1];
}

function toSlug(string $str): string {
    $str = mb_strtolower(trim($str), 'UTF-8');
    $map = ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','à'=>'a','â'=>'a','î'=>'i','ï'=>'i','ô'=>'o','ù'=>'u','û'=>'u','ç'=>'c','œ'=>'oe','æ'=>'ae'];
    $str = strtr($str, $map);
    return preg_replace('/[^a-z0-9]+/', '-', $str);
}

$themeSlug = toSlug($menu['theme_nom'] ?? 'classique');
$regimeSlug = toSlug($menu['regime_nom'] ?? 'classique');
$prixTotal = (float)($menu['prix_total'] ?? ($menu['prix_par_personne'] * $menu['nombre_personne_minimum']));
$image = $menu['image'] ?? ('menu-' . $themeSlug . '.jpg');

$themeBadgeClass = [
    'noel'      => 'badge-theme--noel',
    'paques'    => 'badge-theme--paques',
    'classique' => 'badge-theme--classique',
    'evenement' => 'badge-theme--evenement',
];
$regimePillClass = [
    'classique'  => 'regime--classique',
    'vegetarien' => 'regime--vegetarien',
    'vegan'      => 'regime--vegan',
];
$badgeClass = $themeBadgeClass[$themeSlug] ?? 'badge-theme--classique';
$pillClass  = $regimePillClass[$regimeSlug] ?? 'regime--classique';

$platsParCategorie = [];
if(!empty($menu['plats'])) {
    foreach($menu['plats'] as $plat) {
        $platsParCategorie[$plat['categorie']][] = $plat;
    }
}

?>

<!-- HERO DETAILS -->

<section class="detail-hero">
    <div class="detail-hero__bg" style="background-image: url('<?= $rootPath ?>assets/images/<?= htmlspecialchars($image) ?>')"></div>
    <div class="detail-hero__overlay"></div>
    <div class="container detail-hero__content">
        <a href="<?= $rootPath ?>pages/menus.php" class="detail-back">
            ← Retour aux menus
        </a>
        <div class="detail-hero__badges">
            <span class="badge-theme <?= $badgeClass ?>"><?= htmlspecialchars($menu['theme_nom'] ?? 'Classique') ?></span>
            <span class="regime-pill <?= $pillClass ?>"><?= htmlspecialchars($menu['regime_nom'] ?? 'Classique') ?></span>
        </div>
        <h1 class="detail-hero__title"><?= htmlspecialchars($menu['titre']) ?></h1>
        <div class="detail-hero__meta">
            <span class="detail-meta-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                À partir de <strong><?= (int)$menu['nombre_personne_minimum'] ?>personnes</strong>
            </span>
        </div>
    </div>
</section>

<!-- CONTENU PRINCIPAL -->

<section class="section-detail">
    <div class="container">
        <div class="detail-layout">

        <!-- Colonne principale -->
         <div class="detail-main">

         <!-- Description -->
          <div class="detail-block">
            <h2 class="detail-block--title">À propos de ce menu</h2>
            <p class="detail-desc"><?= htmlspecialchars($menu['description']) ?></p>
          </div>

          <!-- Composition -->
           <?php if (!empty($platsParCategorie)): ?>
            <div class="detail-block">
                <h2 class="detail-block__title">Composition du menu</h2>
                <div class="detail-plats">
                    <?php foreach ($platsParCategorie as $categorie => $plats): ?>
                        <div class="plats-categorie">
                            <h3 class="plats-categorie__titre">
                                <?= htmlspecialchars($categorie) ?>
                            </h3>
                            <ul class="plats-liste">
                                <?php foreach ($plats as $plat): ?>
                                    <li class="plat-item">
                                        <div class="plat-item__nom"><?= htmlspecialchars($plat['nom']) ?></div>
                                        <?php if(!empty($plat['desc'])): ?>
                                            <div class="plat-item__desc"><?= htmlspecialchars($plat['desc']) ?></div>
                                            <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Conditions -->
             <?php if(!empty($menu['conditions'])): ?>
                <div class="detail-block">
                    <h2 class="detail-block__title">Conditions & Informations</h2>
                    <div class="detail-conditions">
                        <p><?= htmlspecialchars($menu['conditions']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
         </div>

         <!-- SIDEBAR -->

         <aside class="detail-sidebar">
            <div class="detail-card-prix">
                <div class="detail-card-prix__header">
                    <span class="detail-card-prix__label">Total estimé</span>
                    <div class="detail-card-prix__montant">
                        <?= number_format($prixTotal, 2, ',', ' ') ?> €
                    </div>
                    <div class="detail-card-prix__base">
                        soit <?= number_format($menu['prix_par_personne'], 2, ',', ' ') ?> €
                    </div>
                </div>
                <div class="detail-card-prix__body">
                    <div class="detail-card-info">
                        <span class="detail-card-info__label">Thème</span>
                        <span class="badge-theme <?= $badgeClass ?>"><?= htmlspecialchars($menu['theme_nom'] ?? '-') ?></span>
                    </div>
                    <div class="detail-card-info">
                        <span class="detail-card-info__label">Régime</span>
                        <span class="regime-pill <?= $pillClass ?>"><?= htmlspecialchars($menu['regime_nom'] ?? '-') ?></span>
                    </div>
                    <div class="detail-card-info">
                        <span class="detail-card-info__label">Personne min.</span>
                        <span class="detail-card-info__val"><?= (int)$menu['nombre_personne_minimum'] ?> pers.</span>
                    </div>
                </div>
                <div class="detail-card-prix__footer">
                    <a href="<?= $rootPath ?>pages/contact.php?menu=<?= (int)$menu['menu_id'] ?>" class="btn btn-vg-primary w-100">Demander un devis →</a>
                    <a href="<?= $rootPath ?>pages/menus.php" class="btn btn-vg-secondary w-100 mt-2">← Voir tous les menus</a>
                </div>
            </div>

            <!-- Bloc besoin personnalisation -->
             <div class="detail-card-custom">
                <h3>Menu sur mesure ?</h3>
                <p>Nous adaptons chaque formule à vos envies et contraintes alimentaires.</p>
                <a href="<?= $rootPath ?>pages/contact.php" class="btn-or">Nous contacter</a>
             </div>
         </aside>
        </div>
    </div>
</section>

<!-- CTA -->

<section class="section-cta py-5">
    <div class="container">
        <div class="cta-box">
            <div class="cta-deco"></div>
            <h2 class="cta-title">Prêt à régaler vos convives ?</h2>
            <p class="cta-desc">Contactez nous pour réserver ce menu ou en discuter avec notre équipe.</p>
            <div class="cta-actions">
                <a href="<?= $rootPath ?>pages/contact.php?menu=<?= (int)$menu['menu_id'] ?>" class="btn-or">Demander un devis</a>
                <a href="<?= $rootPath ?>pages/menus.php" class="btn-vg-primary">Voir tous les menus</a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

