<?php
$pageTitle = 'Commander un menu';
$roothPath  = '../';
$currentPage = 'commande';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

//Vérifier si l'utilisateur est connecté
session_start();
$estConnecte = isset($_SESSION['user_id']);

//if(!$estConnecte) {
 //   header('Location: ' . $rootPath . 'connexion.php?redirect=commande');
   // exit;
//}
    

//Récupérer le menu pré-sélectionné depuis l'URL
$menuIdPreselect = isset($_GET['menu']) ? (int)$_GET['menu'] : 0;

//Récupérer les menus disponibles
$menus = [];
if(isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT m.menu_id, m.titre, m.nombre_personne_minimum, m.prix_par_personne,
        (m.prix_par_personne * m.nombre_personne_minimum) AS prix_total,
        t.nom AS theme_nom FROM menus m
        LEFT JOIN theme ON t.theme_id = m.theme_id
        ORDER BY m.titre ASC");
        $menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e){
        //Exception
    }
}

//Données fictives si pas de BDD
if(empty($menu)) {
    $menu = [

            ['menu_id' => 1, 'titre' => 'Le grand Festin de Noël', 'nombre_personne_minimum' => 10, 'prix_par_personne' => 32.00, 'prix_total' => 320.00, 'theme_nom' => 'Noël'],
            ['menu_id' => 2, 'titre' => 'Printemps & Pâques', 'nombre_personne_minimum' => 8, 'prix_par_personne' => 80.00, 'prix_total' => 180.00, 'theme_nom' => 'Pâques'],
            ['menu_id' => 3, 'titre' => 'Menu Prestige Classique', 'nombre_personne_minimum' => 20, 'prix_par_personne' => 22.50, 'prix_total' => 450.00, 'theme_nom' => 'Classique'],
            ['menu_id' => 4, 'titre' => 'Coktail Dinatoire Végan', 'nombre_personne_minimum' => 15, 'prix_par_personne' => 14.00, 'prix_total' => 210.00, 'theme_nom' => 'Végan']
    ];
}

//Récupérer les infos de l'utilisateur connecté
$user = ['prenom' => '', 'nom' => '', 'email' => '', 'telephone' => '', 'adresse' => ''];
if(isset($pdo) && $estConnecte) {
    try {
        $stmt = $pdo->prepare("SELECT prenom, nom, email, telephone, adresse
        FROM utilisateurs WHERE utilisateur_id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: $user;
    } catch (Exception $e) {
        //Exception
    }
}

//Données fictives utilisateur pour la démo
if(empty($user['email'])) {
    $user = ['prenom' => 'Marie', 'nom' => 'Dupont', 'email' => 'marie.dupont@email.com', 'telephone' => '06 12 34 56 78', 'adresse' => '12 rue des Roses, 33000 Bordeaux'];
}

$erreur = '';
$succes = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuId = (int)($_POST['menu_id'] ?? 0);
    $nbPersonnes = (int)($_POST['nb_personnes'] ?? 0);
    $datePresta = trim($_POST['date_prestation'] ?? '');
    $heurePresta = trim($_POST['heure_prestation'] ?? '');
    $adressePresta = trim($_POST['adresse_prestation'] ?? '');

    if(!$menuId || !$nbPersonnes ||empty($datePresta) ||empty($heurePresta) || empty($adressePresta)) {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        //Trouver le menu sélectionné
        $menuChoisi = null;
        foreach ($menus as $m) {
            if ($m['menu_id'] === $menuId) {
                $menuChoisi = $m;
                break;
            }
        }

        if($menuChoisi && $nbPersonnes < $menuChoisi['nombre_personne_minimum']) {
            $erreur = 'Le nombre de personnes minimum pour ce menu est de ' . $menuChoisi['nombre_personne_minimum'] . '.';
        } else {
            if(isset($pdo)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO commandes (utilisateur_id, menu_id, nb_personnes, date_prestation, heure_prestation, adresse_prestation, statut, created_at)
                    VALUES (:uid, :mid, :nbp, :date, :heure, :adresse, 'en_attente', NOW())");
                    $stmt->execute([
                        ':uid' => $_SESSION['user_id'],
                        ':mid' => $menuId,
                        ':nbp' => $nbPersonnes,
                        ':date' => $datePresta,
                        ':heure' => $heurePresta,
                    ]);
                    $succes = 'Votre commande a bien été enregistrée ! Vous recevrez un mail de confirmation.';
                } catch (Exception $e) {
                    $erreur = 'Une erreur est survenue. Veuillez réessayer.';
                }
            } else {
                $succes = 'Commande simulée avec succès (mode demo) !';
            }
        }
    }
}

//Construire le JSON des menus pour le JS
$menusJson = json_encode($menus);
?>

<section class="section-commande">
    <div class="auth-bg-deco"></div>

    <div class="container">

    <!-- En-tête -->
     <div class="commande-header">
        <a href="<?= $rootPath ?>pages/menus.php" class="detail-back">← Retour aux menus</a>

        <div class="text-center mt-3">
            <span class="section-label">Réservation</span>
            <h1 class="section-title">Commander un menu</h1>
            <div class="divider-or mx-auto"></div>
        </div>
     </div>

     <?php if($succes): ?>
        <!-- Succès -->
         <div class="commande-succes">
            <div class="commande-succes__icon">✓</div>
            <h2>Commande enregistrée !</h2>
            <p><?= htmlspecialchars($succes) ?></p>

            <div class="commande-succes__actions">
                <a href="<?= $rootPath ?>pages/mon-compte.php" class="btn btn-vg-primary">Voir mes commandes</a>
                <a href="<?= $rootPath ?>pages/menus.php" class="btn btn-vg-secondary">Retour aux menus</a>
            </div>
        </div>

        <?php else: ?>

        <?php if($erreur): ?>
        <div class="auth-alert auth-alert--error mb-4">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($erreur) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="commande-form" id="commande-form" novalidate>
            <div class="commande-layout">

            <!-- COLONNE PRINCIPALE -->
             <div class="commande-main">

             <!-- BLOC 1 : INFOS CLIENT -->
              <div class="commande-bloc">
                <div class="commande-bloc__head">
                    <span class="commande-bloc__num">1</span>
                    <h2 class="commande-bloc__title">Vos informations</h2>
                </div>
                <div class="commande-bloc__body">
                    <p class="commande-autofill-notice">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Ces informations sont pré-remplies depuis votre compte.
                    </p>
                    <div class="commande-field-row">
                        <div class="commande-field">
                            <label class="commande-label">Prénom</label>
                            <input type="text" class="commande-input" value="<?= htmlspecialchars($user['prenom']) ?>" readonly>
                        </div>
                        <div class="commande-field">
                            <label class="commande-label">Nom</label>
                            <input type="text" class="commande-input" value="<?= htmlspecialchars($user['nom']) ?>" readonly>
                        </div>
                    </div>
                    <div class="commande-field-row">
                        <div class="commande-field">
                            <label class="commande-label">E-mail</label>
                            <input type="text" class="commande-input" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        <div class="commande-field">
                            <label class="commande-label">Téléphone</label>
                            <input type="tel" class="commande-input" value="<?= htmlspecialchars($user['telephone']) ?>" readonly>
                        </div>
                    </div>
                </div>
              </div>

              <!-- BLOC 2 : MENU -->
               <div class="commande-bloc">
                <div class="commande-bloc__head">
                    <span class="commande-bloc__num">2</span>
                    <h2 class="commande-bloc__title">Choix du menu</h2>
                </div>
                <div class="commande-bloc__body">
                    <div class="commande-field">
                        <label for="menu_id" class="commande-label">Menu <span class="auth-required">*</span></label>
                        <select name="menu_id" id="menu_id" class="commande_input commande-select" required>
                            <option value="">- Sélectionnez un menu -</option>
                            <?php foreach($menu as $m): ?>
                                <option value="<?= $m['menu_id'] ?>" data-prix="<?= $m['prix_par_personne'] ?>" data-min="<?= $m['nombre_personne_minimum'] ?>" <?= ($menuIdPreselect === $m['menu_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['titre']) ?> - <?= number_format($m['prix_par_personne'], 2, ',', ' ') ?> € / pers.
                                </option>
                                <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="commande-field">

                    <label for="nb-personnes" class="commande-label">Nombre de personnes <span class="auth-required">*</span></label>
                    <div class="commande-nb-wrap">
                        <button type="button" class="commande-nb-btn" id="nb-moins">-</button>
                        <input type="number" id="nb_personnes" name="nb_personnes" class="commande_input commande-nb-input" value="1" min="1" required>
                        <button type="button" class="commande-nb-btn" id="nb-plus">+</button>
                    </div>
                    <span class="commande-nb-hint"></span>
                    </div>
                </div>
               </div>

               <!-- BLOC 3 : PRESTATION -->
                <div class="commande-bloc">
                    <div class="commande-bloc__head">
                        <span class="commande-bloc__num">3</span>
                        <h2 class="commande-bloc__title">Lieu & date de la prestation</h2>
                    </div>
                    <div class="commande-bloc__body">
                        <div class="commande-field-row">
                            <div class="commande-field">
                                <label for="date_prestation" class="commande-label">Date <span class="auth-required">*</span></label>
                                <input type="date" id="date_prestation" name="date_prestation" class="commande_input" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                            <div class="commande-field">
                                <label for="heure_prestation" class="commande-label">Heure souhaitée <span class="auth-required">*</span></label>
                                <input type="time" id="heure_prestation" name="heure_prestation" class="commande-input" required>
                            </div>
                        </div>
                        <div class="commande-field">
                            <label for="adresse_prestation" class="commande-label">Adresse de livraison <span class="auth-required">*</span></label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <input type="text" id="adresse_prestation" name="adresse_prestation" class="commande-input" placeholder="12 rue des Lilas, 75001 Paris" value="<?= htmlspecialchars($_POST['adresse_prestation'] ?? '') ?>" required>
                            </div>
                            <span class="commande-livraison-info" id="livraison-info"></span>
                        </div>
                        <button type="button" class="commande-btn-calcul" id="btn-calcul-livraison">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Calculer les frais de livraison
                        </button>
                    </div>
                </div>
             </div>

             <!--SIDEBAR RÉCAP -->
             <aside class="commande-sidebar">
                <div class="commande-recap">
                    <div class="commande-recap__header">
                        <span class="commande-recap__label">Récapitulatif</span>
                    </div>
                    <div class="commande-recap__body" id="recap-body">
                        <div class="reca-ligne">
                            <span class="recap-ligne__lib">Menu sélectionné</span>
                            <span class="recap-ligne__val" id="recap-menu">-</span>
                        </div>
                        <div class="recap-ligne">
                            <span class="recap-ligne__lib">Nombre de personnes</span>
                            <span class="recap-ligne__val" id="recap-nb">-</span>
                        </div>
                        <div class="recap-ligne">
                            <span class="recap-ligne__lib">Prix menu</span>
                            <span class="recap-ligne__val" id="recap-prix-menu">-</span>
                        </div>
                        <div class="recap-ligne" id="recap-remise-ligne" style="display: none;">
                            <span class="recap-ligne__lib recap-ligne__lib--remise">Réduction 10%</span>
                            <span class="recap-ligne__val recap-ligne__val--remise" id="recap-remise">-</span>
                        </div>
                        <div class="recap-ligne">
                            <span class="recap-ligne__lib">Frais de livraison</span>
                            <span class="recap-ligne__val" id="recap-livraison">À calculer</span>
                        </div>
                        <div class="recap-total">
                            <span>Total estimé</span>
                            <span id="recap-total">-</span>
                        </div>

                        <!-- Note conditions -->
                         <div class="recap-conditions" id="recap-conditions" style="display: none;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span id="recap-conditions-text"></span>
                         </div>
                    </div>
                    <div class="commande-recap__footer">
                        <button type="submit" class="auth-btn" id="btn-commander">Confirmer la commande
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                        <p class="recap-mention">En confirmant, vous acceptez nos <a href="<?= $rootPath ?>pages/cvg.php" class="auth-link" target="_blank">CGV</a>.</p>
                    </div>
                </div>
             </aside>
            </div>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>