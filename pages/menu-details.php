<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

$pageTitle = 'Détail du Menu';
$rootPath = '../';
$currentPage = 'menus';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$menu = null;
$platsParCategorie = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(isset($pdo) && $id > 0) {
    try {
        $stmt = $pdo->prepare("
        SELECT m.*,
        t.libelle AS theme_nom,
        r.libelle AS regime_nom,
        mi.url AS image_url
        FROM menu m
        LEFT JOIN theme t ON m.theme_id = t.theme_id
        LEFT JOIN regime r ON m.regime_id = r.regime_id
        LEFT JOIN menu_image mi ON m.menu_id = mi.menu_id AND mi.ordre = 1
        WHERE m.menu_id = :id AND m.actif = 1
        ");
        $stmt->execute([':id' => $id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if($menu) {
            //Récupérer les plats associés à ce menu
            $stmtPlats = $pdo->prepare("
                        SELECT p.titre_plat, p.description, p.categorie
                        FROM plat p
                        INNER JOIN menu_plat mp ON p.plat_id = mp.plat_id
                        WHERE mp.menu_id = :id
                        AND p.actif = 1
                        ORDER BY FIELD(p.categorie, 'Entrée', 'Plat', 'Fromage', 'Dessert', 'Boisson')
                        ");
            $stmtPlats->execute([':id' => $id]);
            $plats_db = $stmtPlats->fetchAll(PDO::FETCH_ASSOC);

            foreach($plats_db as $plat) {
                $platsParCategorie[$plat['categorie']][] = [
                    'nom' => $plat['titre_plat'],
                    'desc' => $plat['description']
                ];
            }
        }
    } catch (Exception $e){
        $erreur = "Erreur : " . $e->getMessage();
    }
}

// Données fictives si pas de BDD ou menu introuvable
if (!$menu) {
   echo "<div class='container'><p>Menu non trouvé.</p></div>";
   require_once '../includes/footer.php';
   exit;
}

$prixTotal = (float)($menu['prix_par_personne'] * $menu['nombre_personne_minimum']);

$imagePath = !empty($menu['image_url']) ? $rootPath . $menu['image_url'] : $rootPath . "assets/images/menu-prestige.jpg";

$themeLabel = $menu['theme_nom'] ?? 'Classique';
$regimeLabel = $menu['regime_nom'] ?? 'Classique';

$themeSlug = strtolower(trim($themeLabel));
$themeSlug = str_replace([' ', '/'], '-', $themeSlug);
$themeSlug = str_replace([' ', 'î', 'é', 'è', 'ê', 'à'], ['-', 'i', 'e', 'e', 'e', 'a'], $themeSlug);
$regimeSlug = strtolower(trim($regimeLabel));
$regimeSlug = str_replace(' ', '-', $regimeSlug);

$badgeClass = 'badge-theme--' . $themeSlug;
$pillClass = 'regime--' . $regimeSlug;

?>

<!-- HERO DETAILS -->

<section class="detail-hero">
    <div class="detail-hero__bg" style="background-image: url('<?= $imagePath ?>');"></div>
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
                À partir de <strong><?= (int)$menu['nombre_personne_minimum'] ?> personnes</strong>
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
                    <a href="<?= $rootPath ?>pages/commande.php?menu=<?= (int)$menu['menu_id'] ?>" class="btn btn-vg-primary w-100">Commander →</a>
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
                <a href="<?= $rootPath ?>pages/contact.php?menu=<?= (int)$menu['menu_id'] ?>" class="btn-or">Nous contacter</a>
                <a href="<?= $rootPath ?>pages/menus.php" class="btn-vg-primary">Voir tous les menus</a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

