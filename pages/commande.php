<?php

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
exiger_connexion();

$pageTitle = 'Commander un menu';
$rootPath  = '../';
$currentPage = 'commande';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

//Vérifier si l'utilisateur est connecté
$estConnecte = isset($_SESSION['user_id']);


//Récupérer le menu pré-sélectionné depuis l'URL
$menuIdPreselect = isset($_GET['menu']) ? (int)$_GET['menu'] : 0;

//Récupérer les menus disponibles
$menus = [];
if(isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT menu_id, titre, nombre_personne_minimum, prix_par_personne
        FROM menu
        WHERE actif = 1
        ORDER BY titre ASC");
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e){
        //Exception
        $erreur = "Erreur SQL : " . $e->getMessage();
    }
}

//Données fictives si pas de BDD
if(empty($menus)) {
    $menus = [

            ['menu_id' => 1, 'titre' => 'Le grand Festin de Noël', 'nombre_personne_minimum' => 10, 'prix_par_personne' => 32.00, 'prix_total' => 320.00, 'theme_nom' => 'Noël'],
            ['menu_id' => 2, 'titre' => 'Printemps & Pâques', 'nombre_personne_minimum' => 8, 'prix_par_personne' => 80.00, 'prix_total' => 180.00, 'theme_nom' => 'Pâques'],
            ['menu_id' => 3, 'titre' => 'Menu Prestige Classique', 'nombre_personne_minimum' => 20, 'prix_par_personne' => 22.50, 'prix_total' => 450.00, 'theme_nom' => 'Classique'],
            ['menu_id' => 4, 'titre' => 'Coktail Dinatoire Végan', 'nombre_personne_minimum' => 15, 'prix_par_personne' => 14.00, 'prix_total' => 210.00, 'theme_nom' => 'Végan']
    ];
}

//Récupérer les infos de l'utilisateur connecté
$user = ['prenom' => '',
        'nom' => '',
        'email' => '',
        'telephone' => '',
        'adresse' => ''];

if(isset($_SESSION['user_id'])) {
    try {
        $stmtU = $pdo->prepare("SELECT nom, prenom, email, telephone, adresse_postale, ville
                                FROM utilisateur
                                WHERE utilisateur_id = ?");
        $stmtU->execute([$_SESSION['user_id']]);
        $userInfo = $stmtU->fetch(PDO::FETCH_ASSOC);

        if($userInfo) {
            $user = $userInfo;
        }
    } catch (Exception $e) {
        //Exception
    }
}

$erreur = '';
$succes = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuId = (int)($_POST['menu_id'] ?? 0);
    $nbPersonnes = (int)($_POST['nb_personnes'] ?? 0);
    $datePresta = trim($_POST['date_prestation'] ?? '');
    $heurePresta = trim($_POST['heure_prestation'] ?? '');
    $adressePresta = trim($_POST['adresse_prestation'] ?? '');
    $villePresta = trim($_POST['ville_prestation'] ?? '');
    $pretMateriel = isset($_POST['pret_materiel']) ? 1 : 0;

    if(!$menuId || $nbPersonnes <= 0 ||empty($datePresta) ||empty($heurePresta) || empty($adressePresta) ||empty($villePresta)) {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
        $pdo->beginTransaction();

        //Récupérer le menu en BDD pour avoir le vrai prix
        $stmtM = $pdo->prepare("SELECT prix_par_personne, nombre_personne_minimum FROM menu WHERE menu_id = ? AND actif = 1");
        $stmtM->execute([$menuId]);
        $menuInfos = $stmtM->fetch();

        if(!$menuInfos) {
            throw new Exception("Le menu sélectionné n'est plus disponible.");
        }

        if($nbPersonnes < $menuInfos['nombre_personne_minimum']) {
            throw new Exception("Le minimum de personnes n'est pas atteint.");
        }

        //Calculs financier
        $prixUnitaire = (float)$menuInfos['prix_par_personne'];
        $sousTotal = $prixUnitaire * $nbPersonnes;

        //FRAIS DE LIVRAISON
        $seuilGratuite = 200;
        $fraisFixes = 15.00;

        if($sousTotal >= $seuilGratuite) {
            $fraisLivraison = 0;
        } else {
            $fraisLivraison = $fraisFixes;
        }

        //Logique de remise
        $remise = ($sousTotal > 300) ? ($sousTotal * 0.10) : 0;
        $totalFinal = $sousTotal - $remise + $fraisLivraison;

        $stmtC = $pdo->prepare("INSERT INTO commande (
                                    utilisateur_id,
                                    statut_id,
                                    date_commande,
                                    date_prestation,
                                    heure_livraison,
                                    adresse_livraison,
                                    ville_livraison,
                                    pret_materiel,
                                    nombre_personnes,
                                    prix_total,
                                    prix_livraison,
                                    created_at
                                    ) VALUES (?, 1, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmtC->execute([
            $_SESSION['user_id'],
            $datePresta,
            $heurePresta,
            $adressePresta,
            $villePresta,
            $pretMateriel,
            $nbPersonnes,
            $totalFinal,
            $fraisLivraison
        ]);

        $commandeId = $pdo->lastInsertId();

        //Insertion dans 'commande_menu'
        $stmtCM = $pdo->prepare("INSERT INTO commande_menu (commande_id, menu_id, quantite, prix_unitaire)
                                VALUES (?, ?, ?, ?)");
        $stmtCM->execute([$commandeId, $menuId, $nbPersonnes, $prixUnitaire]);

        $pdo->commit();
        $succes = "Votre commande n°$commandeId a été validée avec succès !";
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erreur = $e->getMessage();
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
                        <select name="menu_id" id="menu_id" class="commande-input commande-select" required>
                            <option value="">- Sélectionnez un menu -</option>
                            <?php foreach($menus as $m): ?>
                                <option value="<?= $m['menu_id'] ?>" data-prix="<?= $m['prix_par_personne'] ?>" data-min="<?= $m['nombre_personne_minimum'] ?>" <?= ($menuIdPreselect === $m['menu_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['titre']) ?> - <?= number_format($m['prix_par_personne'], 2, ',', ' ') ?> € / pers.
                                </option>
                                <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="commande-field">

                    <label for="nb_personnes" class="commande-label">Nombre de personnes <span class="auth-required">*</span></label>
                    <div class="commande-nb-wrap">
                        <button type="button" class="commande-nb-btn" id="nb-moins">-</button>
                        <input type="number" id="nb_personnes" name="nb_personnes" class="commande-input commande-nb-input" value="1" min="1" inputmode="numeric" required>
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
                                <input type="date" id="date_prestation" name="date_prestation" class="commande-input" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                            <div class="commande-field">
                                <label for="heure_prestation" class="commande-label">Heure souhaitée <span class="auth-required">*</span></label>
                                <input type="time" id="heure_prestation" name="heure_prestation" class="commande-input" required>
                            </div>
                        </div>
                        <div class="commande-field-row">
                            <div class="commande-field">
                                <label for="adresse_prestation" class="commande-label">Adresse de livraison <span class="auth-required">*</span></label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <input type="text" id="adresse_prestation" name="adresse_prestation" class="commande-input" placeholder="12 rue des Lilas" value="<?= htmlspecialchars($_POST['adresse_prestation'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="commande-field">
                                <label for="ville_prestation" class="commande-label">Ville <span class="auth-required">*</span></label>
                                <input type="text" id="ville_prestation" name="ville_prestation" class="commande-input" placeholder="Paris" value="<?= htmlspecialchars($_POST['ville_prestation'] ?? $user['ville']) ?>" required>
                            </div>
                        </div>
                        <span class="commande-livraison-info" id="livraison-info"></span>

                        <div class="commande-option-item mt-3 mb-3">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" id="pret_materiel" name="pret_materiel" value="1">
                                <label for="pret_materiel" class="commande-label ms-3">
                                    Besoin de prêt de matériel (Vaiselle, couverts, tables...)
                                </label>
                            </div>
                            <p style="font-size: 0.85rem; color: #555; margin-left: 28px; margin-top: 5px;">
                                Service gratuit. Nous inclurons le nécessaire selon le nombre de convives.
                            </p>
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
                        <div class="recap-ligne">
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
                        <p class="recap-mention">En confirmant, vous acceptez nos <a href="<?= $rootPath ?>pages/cgv.php" class="auth-link" target="_blank">CGV</a>.</p>
                    </div>
                </div>
             </aside>
            </div>
        </form>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Sélection des éléments
    const menuSelect = document.getElementById('menu_id');
    const nbInput = document.getElementById('nb_personnes');
    const btnMoins = document.getElementById('nb-moins');
    const btnPlus = document.getElementById('nb-plus');
    const hint = document.querySelector('.commande-nb-hint');

    // Éléments de la sidebar récapitulative
    const recapMenu = document.getElementById('recap-menu');
    const recapNb = document.getElementById('recap-nb');
    const recapPrix = document.getElementById('recap-prix-menu');
    const recapLivraison = document.getElementById('recap-livraison');
    const recapTotal = document.getElementById('recap-total');
    const recapRemiseLigne = document.getElementById('recap-remise-ligne');
    const recapRemiseVal = document.getElementById('recap-remise');

    if(!menuSelect || !nbInput) return;

    const dateInput = document.getElementById('date_prestation');
    if(dateInput) dateInput.min = new Date().toISOString().split('T')[0];

    // 2. La fonction de calcul globale
    function updateAll() {
        const selectedOption = menuSelect.options[menuSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value !== "") {
            // --- GESTION DU MINIMUM ---
            const minAllowed = parseInt(selectedOption.dataset.min) || 1;
            const prixUnit = parseFloat(selectedOption.dataset.prix) || 0;
            
            nbInput.min = minAllowed;
            // Si l'utilisateur a saisi un chiffre trop petit, on remet le minimum
            if (parseInt(nbInput.value) < minAllowed) {
                nbInput.value = minAllowed;
            }
            
            const nb = parseInt(nbInput.value);
            if (hint) hint.textContent = `Minimum requis : ${minAllowed} personnes.`;

            // --- CALCULS ---
            const sousTotal = prixUnit * nb;
            // Remise de 10% si le total menu dépasse 300€
            const remise = (sousTotal > 300) ? (sousTotal * 0.10) : 0;
            // Livraison gratuite si le total menu dépasse 200€
            const seuilGratuite = 200;
            const fraisLivraison = (sousTotal >= seuilGratuite) ? 0 : 15.00;
            const totalFinal = sousTotal - remise + fraisLivraison;
            const formatFR = (num) => num.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + " €";

            // --- MISE À JOUR VISUELLE (SIDEBAR) ---
            if (recapMenu) recapMenu.textContent = selectedOption.text.split('-')[0].trim();
            if (recapNb) recapNb.textContent = nb + " pers.";
            if (recapPrix) recapPrix.textContent = formatFR(sousTotal);
            
            // Affichage/Masquage de la ligne remise
            if (recapRemiseLigne) {
                if (remise > 0) {
                    recapRemiseLigne.style.display = 'flex';
                    recapRemiseVal.textContent = "-" + formatFR(remise);
                } else {
                    recapRemiseLigne.style.display = 'none';
                }
            }

            // Affichage livraison
            if (recapLivraison) {
                recapLivraison.textContent = (fraisLivraison === 0) ? "Gratuit" : formatFR(fraisLivraison);
            }

            // Total final
            if (recapTotal) recapTotal.textContent = formatFR(totalFinal);
            
            // Petit message d'info livraison sous l'adresse
            const infoLivraison = document.getElementById('livraison-info');
            if (infoLivraison) {
                if (fraisLivraison === 0) {
                    infoLivraison.textContent = "Bravo ! Livraison offerte.";
                    infoLivraison.style.color = "green";
                } else {
                    const restant = seuilGratuite - sousTotal;
                    infoLivraison.textContent = `Plus que ${restant.toFixed(2)}€ pour la livraison gratuite.`;
                    infoLivraison.style.color = "inherit";
                }
            }
        }
    }

    // 3. Événements (Clics et Changements)
    
    // Bouton Plus
    btnPlus.addEventListener('click', function() {
        nbInput.value = parseInt(nbInput.value) + 1;
        updateAll(); // On recalcule tout
    });

    // Bouton Moins
    btnMoins.addEventListener('click', function() {
        const minAllowed = parseInt(nbInput.min) || 1;
        if (parseInt(nbInput.value) > minAllowed) {
            nbInput.value = parseInt(nbInput.value) - 1;
            updateAll(); // On recalcule tout
        }
    });

    // Changement de menu ou saisie manuelle du nombre
    menuSelect.addEventListener('change', updateAll);
    nbInput.addEventListener('input', updateAll);

    // Initialisation au chargement de la page
    updateAll();
});
</script>

<?php require_once '../includes/footer.php'; ?>