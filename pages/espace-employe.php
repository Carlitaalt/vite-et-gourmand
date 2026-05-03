<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';
exiger_role('employe');

$pageTitle = 'Espace Employé';
$rootPath = '../';
$currentPage = 'espace-employe';

//Traitement POST
$erreur = "";
$succes = "";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch($_POST['action']) {

        case 'update_statut':
            $commande_id = (int)$_POST['commande_id'];
            $nouveau_statut = (int)$_POST['nouveau_statut'];
            try {
                $pdo->beginTransaction();

                //Vérifier si le statut est différent
                $stmtCheck = $pdo->prepare("SELECT statut_id FROM commande WHERE commande_id = ?");
                $stmtCheck->execute([$commande_id]);
                $actuel = $stmtCheck->fetchColumn();

                if ($actuel != $nouveau_statut){
                    $stmt = $pdo->prepare("UPDATE commande SET statut_id = ? WHERE commande_id = ?");
                    $stmt->execute([$nouveau_statut, $commande_id]);

                    $stmtHist = $pdo->prepare("INSERT INTO commande_statut (commande_id, statut_id, date_modification) VALUES (?, ?, NOW())");
                    $stmtHist->execute([$commande_id, $nouveau_statut]);

                    $pdo->commit();
                    $succes = "Le statut de la commande #$commande_id a été mis à jour.";
                } else {
                    $pdo->rollback();
                }
            } catch (Exception $e){
                if($pdo->inTransaction()) $pdo->rollBack();
                $erreur = "Erreur statut : " . $e->getMessage();
            }
            break;

        case 'annuler_commande':
            $commande_id = (int)$_POST['commande_id'];
            $motif = !empty($_POST['motif_annulation']) ? trim($_POST['motif_annulation']) : 'Annulé par le personnel';
            $mode_contact = !empty($_POST['mode_contact']) ? $_POST['mode_contact'] : 'Téléphone';
            $statut_annule = 8;

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE commande SET statut_id = ?, motif_annulation = ?, mode_contact = ? WHERE commande_id = ?");
                $stmt->execute([$statut_annule, $motif, $mode_contact, $commande_id]);

                $stmtHist = $pdo->prepare("INSERT INTO commande_statut (commande_id, statut_id, date_modification) VALUES (?, ?, NOW())");
                $stmtHist->execute([$commande_id, $statut_annule]);

                $pdo->commit();
                $succes = "Commande annulée et client notifié.";
            } catch (Exception $e) {
                if($pdo->inTransaction()) $pdo->rollBack();
                $erreur = "Erreur annulation : " . $e->getMessage();
            }
            break;

            //Gestion des menus
            case 'update_menu':
                $menu_id = !empty($_POST['menu_id']) ? (int)$_POST['menu_id'] : null;
                $titre = trim($_POST['titre']);
                $prix = (float)$_POST['prix'];
                $actif = isset($_POST['actif']) ? (int)$_POST['actif'] : 1;

                try {
                    if($menu_id) {
                        $stmt = $pdo->prepare("UPDATE menu SET titre = ?, prix_par_personne = ?, actif = ? WHERE menu_id = ?");
                        $stmt->execute([$titre, $prix, $actif, $menu_id]);
                        $succes = "Le menu a été mis à jour.";
                    } else {
                        //Création d'un nouveau menu
                        $stmt = $pdo->prepare("INSERT INTO menu (titre, prix_par_personne, actif) VALUES (?, ?, ?)");
                        $stmt->execute([$titre, $prix, $actif]);
                        $succes = "Le nouveau menu a été créé.";
                    }

                    //Redirection pour rafraîchir les données et vider le POST
                    header("Location: espace-employe.php?tab=menus");
                    exit();
                } catch (Exception $e) {
                    $erreur = "Erreur menu : " . $e->getMessage();
                }
                break;

            //Supprimer le menu
            case 'delete_menu':
                $menu_id = (int)$_POST['menu_id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM menu WHERE menu_id = ?");
                    $stmt->execute([$menu_id]);
                    header("Location: espace-employe.php?tab=menus");
                    exit();
                } catch (Exception $e) {
                    $erreur = "Impossible de supprimer ce menu";
                }
                break;

            //Gestion des plats
            case 'update_plat':
                $plat_id = !empty($_POST['plat_id']) ? (int)$_POST['plat_id'] : null;
                $titre = trim($_POST['titre_plat']);
                $desc = trim($_POST['description']);
                $cat = $_POST['categorie'];
                $actif = isset($_POST['actif']) ? (int)$_POST['actif'] : 1;
                $menu_id = (int)$_POST['menu_id'];

                try {
                    $pdo->beginTransaction();
                    if($plat_id) {
                        //Mise à jour plat existant
                        $stmt = $pdo->prepare("UPDATE plat SET titre_plat = ?, description = ?, categorie = ?, actif = ? WHERE plat_id = ?");
                        $stmt->execute([$titre, $desc, $cat, $actif, $plat_id]);
                    } else {
                        //Création nouveau plat
                        $stmt = $pdo->prepare("INSERT INTO plat (titre_plat, description, categorie, actif) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$titre, $desc, $cat, $actif]);
                        $plat_id = $pdo->lastInsertId();

                        //Lien avec le menu
                        $stmtLien = $pdo->prepare("INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)");
                        $stmtLien->execute([$menu_id, $plat_id]);
                    }
                    $pdo->commit();
                    $succes = "Le plat a été enregistré.";
                } catch(Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $erreur = "Erreur plat : " . $e->getMessage();
                }
                break;

        //modération des avis
        case 'valider_avis':
            $avis_id = (int)$_POST['avis_id'];
            $stmt = $pdo->prepare("UPDATE avis SET statut_avis_id = 2 WHERE avis_id = ?");
            $stmt->execute([$avis_id]);
            header("Location: espace-employe.php?tab=avis-employe");
            exit();
            $succes = "L'avis est désormais visible en ligne.";
            break;

        case 'refuser_avis':
            $avis_id = (int)$_POST['avis_id'];
            $stmt = $pdo->prepare("UPDATE avis SET statut_avis_id = 3 WHERE avis_id = ?");
            $stmt->execute([$avis_id]);
            header("Location: espace-employe.php?tab=avis-employe");
            exit();
            $succes = "L'avis a été rejeté.";
            break;

        //Mise à jour horaires
        case 'update_horaires':
            try {
                $pdo->beginTransaction();
                //Récupérer les données envoyé par le formulaire
                foreach ($_POST['debut'] as $jour => $heure_ouv) {
                    $heure_fer = $_POST['fin'][$jour];
                    $estOuvert = isset($_POST['ouvert'][$jour]);

                    $final_ouv = $estOuvert && !empty($heure_ouv) ? $heure_ouv : '00:00:00';
                    $final_fer = $estOuvert && !empty($heure_fer) ? $heure_fer : '00:00:00';
                 
                    $stmt = $pdo->prepare("UPDATE horaire SET heure_ouverture = ?, heure_fermeture = ? WHERE jour = ?");
                    $stmt->execute([$final_ouv, $final_fer, $jour]);
                }

                $pdo->commit();
                $succes = "Les horaires du restaurant ont été mis à jour avec succès.";

            } catch (Exception $e) {
                if($pdo->inTransaction()) $pdo->rollBack();
                $erreur = "Erreur lors de la mise à jour : " . $e->getMessage();
            }
            break;

            default:
            $erreur = "Action non autorisée ou inconnue.";
            break;
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

//Données employé connecté
$id_session = $_SESSION['user_id'] ?? null;
if(!$id_session) {
    header('Location: ' . $rootPath . 'pages/accueil.php');
    exit();
}

$sql = "SELECT u.prenom, u.nom, u.email, e.poste, e.date_embauche
        FROM utilisateur u
        INNER JOIN employes e ON u.utilisateur_id = e.utilisateur_id
        WHERE u.utilisateur_id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_session]);
$employe = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$employe) {
    die("Erreur: Compte employé non trouvé. Veuillez contacter l'administrateur.");
}
$employe['role_display'] = $employe['poste'] ?? 'Employé';

if(isset($_GET['statut_ok'])) $succes = 'Le statut a été mis à jour.';


//Récupération des commandes
$stmtC = $pdo->query("SELECT c.*, u.prenom as client_prenom, u.nom as client_nom, u.email as client_email, u.telephone as client_telephone,
            (SELECT m.titre
            FROM commande_menu cm
            JOIN menu m ON cm.menu_id = m.menu_id
            WHERE cm.commande_id = c.commande_id
            LIMIT 1) AS menu_titre
            FROM commande c
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            ORDER BY c.date_prestation DESC");
$commandes = $stmtC->fetchAll(PDO::FETCH_ASSOC);

//Récupération des avis
$stmtA = $pdo->prepare("SELECT a.avis_id,
                                a.note,
                                a.description as commentaire,
                                a.created_at as date,
                                a.statut_avis_id,
                                u.prenom,
                                u.nom,
                                CONCAT(u.prenom, ' ', u.nom) as client,
                                (SELECT m.titre
                                FROM commande_menu cm
                                JOIN menu m ON cm.menu_id = m.menu_id
                                WHERE cm.commande_id = a.commande_id LIMIT 1) as menu_titre
                        FROM avis a 
                        JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
                        ORDER BY a.created_at DESC
                        ");

$stmtA->execute();
$avis = $stmtA->fetchAll(PDO::FETCH_ASSOC);

//Récupération des PLATS
$stmtP = $pdo->query("SELECT * FROM plat ORDER BY categorie, titre_plat");
$plats = $stmtP->fetchAll(PDO::FETCH_ASSOC);

//Récupération des menus
$stmtM = $pdo->query("SELECT m.*, (SELECT COUNT(*) FROM menu_plat mp WHERE mp.menu_id = m.menu_id) as nb_plats
                    FROM menu m");
$menus = $stmtM->fetchAll(PDO::FETCH_ASSOC);

//Récuparation des horaires
$stmtH = $pdo->query("SELECT * FROM horaire ORDER BY horaire_id ASC");
$rows = $stmtH->fetchAll(PDO::FETCH_ASSOC);
$horaires = [];
foreach($rows as $r){
    $horaires[] = [
        'horaire_id' => $r['horaire_id'],
        'jour' => $r['jour'],
        'ouvert' => ($r['heure_ouverture'] !== '00:00:00' && $r['heure_ouverture'] !== null),
        'debut' => $r['heure_ouverture'],
        'fin' => $r['heure_fermeture']
    ];
}

//Labels statuts pour l'affichage
$statutLabels = [
    1 => 'En attente',
    2 => 'Acceptée',
    3 => 'En préparation',
    4 => 'En cours de livraison',
    5 => 'Livrée',
    6 => 'Retour matériel',
    7 => 'Terminée',
    8 => 'Annulée',
];
 
$statutColors = [
    1 => 'statut--attente',
    2 => 'statut--accepte',
    3 => 'statut--prep',
    4 => 'statut--livraison',
    5 => 'statut--livre',
    6 => 'statut--materiel',
    7 => 'statut--termine',
    8 => 'statut--annule',
];
 
$statutTransitions = [
    1 => [2, 8],
    2 => [3, 8],
    3 => [4],
    4 => [5],
    5 => [6, 7],
    6 => [7]
];

//Stats rapides
$nbEnAttente = count(array_filter($commandes, function($c) {
    return isset($c['statut_id']) && $c['statut_id'] == 1;
}));

$nbEnCours = count(array_filter($commandes, function($c) {
    return isset($c['statut_id']) && in_array($c['statut_id'], [2, 3, 4, 5, 6]);
}));

$nbAvisAttente = count(array_filter($avis, function($a) {
    return isset($a['statut_avis_id']) && $a['statut_avis_id'] == 1;
}));

$caTotal = array_sum(array_column(array_filter($commandes, function($c) {
    return isset($c['statut_id']) && $c['statut_id'] == 7;
}), 'prix_total'));

?>

<section class="section-compte section-employe">
    <div class="auth-bg-deco"></div>
    <div class="container">

    <!-- En tête employé -->
     <div class="compte-header">
        <div class="compte-avatar employe-avatar">
            <?= strtoupper(substr($employe['prenom'], 0, 1) . substr($employe['nom'], 0, 1)) ?>
        </div>
        <div class="compte-header__info">
            <h1 class="compte-header__nom">
                <?= htmlspecialchars($employe['prenom'] . ' ' . $employe['nom']) ?>
                <span class="employe-role-badge">
                    <?= htmlspecialchars($employe['role_display']) ?>
                </span>
            </h1>
            <p class="compte-header__email">
                <?= htmlspecialchars($employe['email']) ?>
            </p>
        </div>
     </div>

     <!-- Stats rapides -->
      <div class="employe-stats">
        <div class="employe-stat-card employe-stat-card--attente">
            <div class="employe-stat__val">
                <?= $nbEnAttente ?>
            </div>
            <div class="employe-stat__label">En attente</div>
        </div>
        <div class="employe-stat-card employe-stat-card--cours">
            <div class="employe-stat__val">
                <?= $nbEnCours ?>
            </div>
            <div class="employe-stat__label">En cours</div>
        </div>
        <div class="employe-stat-card employe-stat-card--avis">
            <div class="employe-stat__val">
                <?= $nbAvisAttente ?>
            </div>
            <div class="employe-stat__label">Avis à valider</div>
        </div>
        <div class="employe-stat-card employe-stat-card--ca">
            <div class="employe-stat__val">
                <?= number_format($caTotal, 0, ',', ' ') ?> €
            </div>
            <div class="employe-stat__label">CA terminé</div>
        </div>
      </div>
      <!-- Alertes -->
        <?php if($succes): ?>
            <div class="auth-alert auth-alert--success mb-3">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($succes) ?>
            </div>
        <?php endif; ?>

        <!-- Onglet -->
         <div class="compte-tabs">
            <button class="compte-tab active" data-tab="commandes">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Commandes
                <?php if($nbEnAttente > 0): ?>
                    <span class="compte-tab__badge">
                        <?= $nbEnAttente ?>
                    </span>
                    <?php endif; ?>
            </button>
            <button class="compte-tab" data-tab="avis-employe">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Avis clients
                <?php if($nbAvisAttente > 0): ?>
                    <span class="compte-tab__badge">
                        <?= $nbAvisAttente ?>
                    </span>
                    <?php endif; ?>
            </button>
            <button class="compte-tab" data-tab="menus">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Menus & Plats
            </button>
            <button class="compte-tab" data-tab="horaires">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Horaires
            </button>
         </div>

         <!-- CONTENU ONGLETS -->
          <div class="compte-tabs-content">

          <!-- ====== ONGLET COMMANDES (identique admin) ====== -->
            <div class="compte-panel" id="tab-commandes">
                <div class="employe-filtres">
                    <div class="employe-filtre-group">
                        <label class="employe-filtre-label">Filtrer par statut</label>
                        <select id="filtre-statut" class="commande-input employe-filtre-select" onchange="filtrerCommandes()">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statutLabels as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="employe-filtre-group">
                        <label class="employe-filtre-label">Rechercher un client</label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" class="auth-input" id="filtre-client" placeholder="Nom, prénom, email..." oninput="filtrerCommandes()">
                        </div>
                    </div>
                </div>
 
                <div class="commandes-liste" id="liste-commandes">
                    <?php foreach ($commandes as $cmd): ?>
                        <div class="commande-item employe-commande-item"
                             data-statut="<?= $cmd['statut_id'] ?>"
                             data-client="<?= strtolower($cmd['client_prenom'] . ' ' . $cmd['client_nom']) ?>"
                             id="admin-cmd-<?= $cmd['commande_id'] ?>">
 
                            <div class="commande-item__header">
                                <div class="commande-item__id">
                                    <span class="commande-item__num">Commande #<?= $cmd['commande_id'] ?></span>
                                    <span class="commande-statut <?= $statutColors[$cmd['statut_id']] ?? '' ?>">
                                        <?= $statutLabels[$cmd['statut_id']] ?? $cmd['statut_id'] ?>
                                    </span>
                                    <?php if (!empty($cmd['materiel_prete'])): ?>
                                        <span class="commande-statut statut--materiel">Matériel prêté</span>
                                    <?php endif; ?>
                                </div>
                                <div class="commande-item__prix"><?= number_format($cmd['prix_total'], 2, ',', ' ') ?> €</div>
                            </div>
 
                            <div class="commande-item__body">
                                <div class="commande-item__info">
                                    <div class="employe-client-bloc">
                                        <div class="employe-client-avatar">
                                            <?= strtoupper(substr($cmd['client_prenom'], 0, 1) . substr($cmd['client_nom'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="employe-client-nom"><?= htmlspecialchars($cmd['client_prenom'] . ' ' . $cmd['client_nom']) ?></div>
                                            <div class="employe-client-contact">
                                                <a href="mailto:<?= htmlspecialchars($cmd['client_email'] ?? '') ?>">
                                                    <?= htmlspecialchars($cmd['client_email'] ?? 'Non renseigné') ?>
                                                </a>
                                                &nbsp;·&nbsp;
                                                <a href="tel:<?= htmlspecialchars($cmd['client_telephone'] ?? '') ?>">
                                                    <?= htmlspecialchars($cmd['client_telephone'] ?? 'Non renseigné') ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="commande-info-row">
                                        <span class="commande-info-label">Menu</span>
                                        <span class="commande-info-val"><?= htmlspecialchars($cmd['menu_titre']) ?></span>
                                    </div>
                                    <div class="commande-info-row">
                                        <span class="commande-info-label">Personnes</span>
                                        <span class="commande-info-val"><?= $cmd['nombre_personnes'] ?? '0' ?> pers.</span>
                                    </div>
                                    <div class="commande-info-row">
                                        <span class="commande-info-label">Date</span>
                                        <span class="commande-info-val"><?= date('d/m/Y', strtotime($cmd['date_prestation'])) ?> à <?= $cmd['heure_livraison'] ?></span>
                                    </div>
                                </div>
 
                                <div class="commande-suivi">
                                    <h4 class="commande-suivi__titre">Historique</h4>
                                    <ul class="suivi-timeline">
                                        <?php
                                        $stmtH = $pdo->prepare("SELECT * FROM commande_statut WHERE commande_id = ? ORDER BY date_modification ASC");
                                        $stmtH->execute([$cmd['commande_id']]);
                                        $historique = $stmtH->fetchAll();
                                        
                                        foreach ($historique as $etape): ?>
                                            <li class="suivi-etape done">
                                                <div class="suivi-etape__dot"></div>
                                                <div class="suivi-etape__content">
                                                    <span class="suivi-etape__label"><?= $statutLabels[$etape['statut_id']]?></span>
                                                    <span class="suivi-etape__date">
                                                        le <?= date('d/m à H:i', strtotime($etape['date_modification'])) ?>
                                                    </span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
 
                            <div class="commande-item__actions employe-commande-actions">
                                <?php if (isset($statutTransitions[$cmd['statut_id']])): ?>
                                    <form method="POST" action="" style="display:inline-flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="action" value="update_statut">
                                        <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                        <select name="nouveau_statut" class="commande-input employe-statut-select">
                                            <?php foreach ($statutTransitions[$cmd['statut_id']] as $s): ?>
                                                <option value="<?= $s ?>"><?= $statutLabels[$s] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-compte-action btn-compte-action--modifier">
                                            Mettre à jour →
                                        </button>
                                    </form>
                                <?php endif; ?>
 
                                <?php if (!in_array($cmd['statut_id'], [7, 8])): ?>
                                    <button type="button"
                                            class="btn-compte-action btn-compte-action--annuler"
                                            onclick="ouvrirAnnulation(<?= $cmd['commande_id'] ?>)">
                                        ✕ Annuler
                                    </button>
                                <?php endif; ?>
                            </div>
 
                            <div class="commande-modif-form employe-annulation-form"
                                 id="annulation-<?= $cmd['commande_id'] ?>" style="display:none;">
                                <form method="POST" action="" class="modif-form">
                                    <input type="hidden" name="action" value="annuler_commande">
                                    <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                    <h4 class="modif-form__titre">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        Annuler — contact client obligatoire
                                    </h4>
                                    <div class="employe-annulation-notice">
                                        Vous devez avoir contacté le client avant toute annulation.
                                    </div>
                                    <div class="commande-field-row">
                                        <div class="commande-field">
                                            <label class="commande-label">Mode de contact *</label>
                                            <select name="mode_contact" class="commande-input" required>
                                                <option value="">— Sélectionner —</option>
                                                <option value="appel_gsm">Appel GSM</option>
                                                <option value="email">E-mail</option>
                                            </select>
                                        </div>
                                        <div class="commande-field">
                                            <label class="commande-label">Date du contact *</label>
                                            <input type="datetime-local" name="date_contact" class="commande-input" required>
                                        </div>
                                    </div>
                                    <div class="commande-field">
                                        <label class="commande-label">Motif *</label>
                                        <textarea name="motif_annulation" class="commande-input avis-textarea"
                                                  placeholder="Motif d'annulation..." rows="3" required></textarea>
                                    </div>
                                    <div class="modif-form__actions">
                                        <button type="submit" class="btn btn-vg-primary">Confirmer</button>
                                        <button type="button" class="btn btn-vg-secondary"
                                                onclick="fermerAnnulation(<?= $cmd['commande_id'] ?>)">Retour</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
 
                <div class="compte-empty" id="no-results" style="display:none;">
                    <h3>Aucune commande trouvée</h3>
                    <p>Modifiez vos filtres pour voir d'autres commandes.</p>
                </div>
            </div>

    <!-- ONGLET AVIS CLIENTS -->

<div class="compte-panel" id="tab-avis-employe">
    <?php
        // On sépare pour l'affichage des titres, mais on garde le même style
        $avisEnAttente = array_filter($avis, fn($a) => (int)$a['statut_avis_id'] === 1);
        $avisTraites = array_filter($avis, fn($a) => in_array((int)$a['statut_avis_id'], [2, 3]));
    ?>

    <!-- SECTION : À VALIDER -->
    <h3 class="employe-section-titre">À valider (<?= count($avisEnAttente) ?>)</h3>
    <div class="commandes-liste">
        <?php if (!empty($avisEnAttente)): ?>
            <?php foreach ($avisEnAttente as $a): ?>
                <div class="commande-item"> <!-- Même classe pour tout le monde -->
                    <div class="commande-item__header">
                        <div class="commande-item__id">
                            <span class="commande-item__num"><?= htmlspecialchars($a['client']) ?></span>
                            <span class="commande-statut statut--attente">En attente</span>
                        </div>
                        <span class="commande-info-label"><?= date('d/m/Y', strtotime($a['date'])) ?></span>
                    </div>
                    <div class="avis-employe-body">
                        <div class="avis-employe-menu"><?= htmlspecialchars($a['menu_titre']) ?></div>
                        <div class="avis-donne__stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= $a['note'] ? 'star--on' : 'star--off' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <p class="avis-donne__texte">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
                    </div>
                    <div class="commande-item__actions">
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="action" value="valider_avis">
                            <input type="hidden" name="avis_id" value="<?= $a['avis_id'] ?>">
                            <button type="submit" class="btn-compte-action btn-compte-action--modifier">✓ Publier</button>
                        </form>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="action" value="refuser_avis">
                            <input type="hidden" name="avis_id" value="<?= $a['avis_id'] ?>">
                            <button type="submit" class="btn-compte-action btn-compte-action--annuler">✕ Refuser</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="compte-empty"><p>Aucun avis en attente.</p></div>
        <?php endif; ?>
    </div>

    <div style="margin: 40px 0;"></div> <!-- Espace entre les deux sections -->

    <!-- SECTION : HISTORIQUE (Même Style) -->
    <h3 class="employe-section-titre">Historique des avis traités</h3>
    <div class="commandes-liste">
        <?php if (!empty($avisTraites)): ?>
            <?php foreach ($avisTraites as $a): ?>
                <div class="commande-item"> <!-- EXACTEMENT LA MÊME CLASSE ICI -->
                    <div class="commande-item__header">
                        <div class="commande-item__id">
                            <span class="commande-item__num"><?= htmlspecialchars($a['client']) ?></span>
                            <?php if ((int)$a['statut_avis_id'] === 2): ?>
                                <span class="commande-statut statut--termine">Publié</span>
                            <?php else: ?>
                                <span class="commande-statut statut--annule">Refusé</span>
                            <?php endif; ?>
                        </div>
                        <span class="commande-info-label"><?= date('d/m/Y', strtotime($a['date'])) ?></span>
                    </div>
                    <div class="avis-employe-body">
                        <div class="avis-employe-menu"><?= htmlspecialchars($a['menu_titre']) ?></div>
                        <div class="avis-donne__stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= $a['note'] ? 'star--on' : 'star--off' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <p class="avis-donne__texte">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
                    </div>
                    <!-- On laisse le bloc actions vide ou on met un petit message pour garder la hauteur du bloc -->
                    <div class="commande-item__actions">
                        <span class="commande-info-label" style="font-style: italic;">Traité le <?= date('d/m à H:i', strtotime($a['date'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="compte-empty"><p>Aucun avis dans l'historique.</p></div>
        <?php endif; ?>
    </div>
</div>
<!-- Onglet Menus & Plats -->

<div class="compte-panel" id="tab-menus">

    <!-- Sous onglets -->
     <div class="employe-sous-tabs">
        <button class="employe-sous-tab active" data-sous="sous-menus">Menus</button>
        <button class="employe-sous-tab" data-sous="sous-plats">Plats</button>
     </div>

     <!-- Sous pannel Menus -->
      <div class="employe-sous-panel active" id="sous-menus">
        <div class="employe-catalogue-header">
            <h3 class="employe-section-titre">Gestion des menus</h3>
            <button class="btn-compte-action btn-compte-action--modifier" onclick="toggleForm('form-nouveau-menu')">
                + Nouveau menu
            </button>
        </div>

        <!-- Formulaire nouveau menu -->
         <div class="commande-modif-form" id="form-nouveau-menu" style="display:none;">
            <form method="POST" action="" class="modif-form">
                <input type="hidden" name="action" value="update_menu">
                <h4 class="modif-form__titre">Nouveau menu</h4>
                <div class="commande-field-row">
                    <div class="commande-field">
                        <label class="commande-label">Titre du menu</label>
                        <input type="text" name="titre" class="commande-input" placeholder="Ex : Menu Été Provencal" required>
                    </div>
                    <div class="commande-field">
                        <label class="commande-label">Prix par personnes (€)</label>
                        <input type="number" name="prix" class="commande-input" step="0.50" min="0" placeholder="25.00" required>
                    </div>
                </div>
                <div class="commande-field">
                    <label class="commande-label">Description courte</label>
                    <textarea name="description" class="commande-input avis-textarea" placeholder="Décrivez le menu..." rows="2" required></textarea>
                </div>
                <div class="modif-form__actions">
                    <button type="submit" class="btn btn-vg-primary">Créer le menu</button>
                    <button type="button" class="btn btn-vg-secondary" onclick="toggleForm('form-nouveau-menu')">
                        Annuler
                    </button>
                </div>
            </form>
         </div>

         <div class="employe-catalogue">
                <?php foreach ($menus as $menu): ?>
                    <div class="employe-catalogue-item">
                        <div class="employe-catalogue-item__header">
                            <div>
                                <span class="commande-item__num"><?= htmlspecialchars($menu['titre']) ?></span>
                                    <span class="commande-statut <?= $menu['actif'] ? 'statut--accepte' : 'statut--annule' ?>">
                                        <?= $menu['actif'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                            </div>
                            <div class="employe-catalogue-item__meta">
                                <span><?= $menu['nb_plats'] ?> plats</span>
                                <span class="commande-item__prix"><?= number_format($menu['prix_par_personne'], 2, ',', ' ') ?> € / pers.</span>
                            </div>
                        </div>
                        <div class="commande-item__actions">
                            <button class="btn-compte-action btn-compte-action--modifier" onclick="toggleForm('form-menu-<?= $menu['menu_id'] ?>')">
                                Modifier
                            </button>
                                    <form method="POST" action="" style="display:inline;"
                                          onsubmit="return confirm('Supprimer ce menu ?')">
                                        <input type="hidden" name="action" value="delete_menu">
                                        <input type="hidden" name="menu_id" value="<?= $menu['menu_id'] ?>">
                                        <button type="submit" class="btn-compte-action btn-compte-action--annuler">Supprimer</button>
                                    </form>
                                </div>
                                <!-- Formulaire modification -->
                                <div class="commande-modif-form" id="form-menu-<?= $menu['menu_id'] ?>" style="display:none;">
                                    <form method="POST" action="" class="modif-form">
                                        <input type="hidden" name="action" value="update_menu">
                                        <input type="hidden" name="menu_id" value="<?= $menu['menu_id'] ?>">
                                        <div class="commande-field-row">
                                            <div class="commande-field">
                                                <label class="commande-label">Titre</label>
                                                <input type="text" name="titre" class="commande-input" value="<?= htmlspecialchars($menu['titre']) ?>">
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Prix / pers. (€)</label>
                                                <input type="number" name="prix" class="commande-input" step="0.50" value="<?= $menu['prix_par_personne'] ?>">
                                            </div>
                                        </div>
                                        <div class="commande-field">
                                            <label class="commande-label">Statut</label>
                                            <select name="actif" class="commande-input">
                                                <option value="1" <?= $menu['actif'] ? 'selected' : '' ?>>Actif</option>
                                                <option value="0" <?= !$menu['actif'] ? 'selected' : '' ?>>Inactif</option>
                                            </select>
                                        </div>
                                        <div class="modif-form__actions">
                                            <button type="submit" class="btn btn-vg-primary">Enregistrer</button>
                                            <button type="button" class="btn btn-vg-secondary"
                                                    onclick="toggleForm('form-menu-<?= $menu['menu_id'] ?>')">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
 
                <!-- Sous-panel Plats -->
                <div class="employe-sous-panel" id="sous-plats">
                    <div class="employe-catalogue-header">
                        <h3 class="employe-section-titre">Gestion des plats</h3>
                        <button class="btn-compte-action btn-compte-action--modifier"
                                onclick="toggleForm('form-nouveau-plat')">
                            + Nouveau plat
                        </button>
                    </div>
 
                    <!-- Formulaire nouveau plat -->
                    <div class="commande-modif-form" id="form-nouveau-plat" style="display:none;">
                        <form method="POST" action="" class="modif-form">
                            <input type="hidden" name="action" value="update_plat">
                            <h4 class="modif-form__titre">Nouveau plat</h4>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label class="commande-label">Nom du plat</label>
                                    <input type="text" name="nom" class="commande-input" placeholder="Ex : Saumon gravlax" required>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Catégorie</label>
                                    <select name="categorie" class="commande-input">
                                        <option value="Entrée">Entrée</option>
                                        <option value="Plat">Plat</option>
                                        <option value="Dessert">Dessert</option>
                                        <option value="Boisson">Boisson</option>
                                    </select>
                                </div>
                            </div>
                            <div class="commande-field">
                                <label class="commande-label">Allergènes</label>
                                <input type="text" name="allergenes" class="commande-input" placeholder="Ex : Gluten, Lait, Œufs">
                            </div>
                            <div class="modif-form__actions">
                                <button type="submit" class="btn btn-vg-primary">Enregistrer</button>
                                <button type="button" class="btn btn-vg-secondary" onclick="toggleForm('form-nouveau-plat')">Annuler</button>
                            </div>
                        </form>
                    </div>
 
                    <div class="employe-catalogue employe-catalogue--plats">
                        <?php foreach ($plats as $plat): ?>
                            <div class="employe-catalogue-item">
                                <div class="employe-catalogue-item__header">
                                    <div>
                                        <span class="commande-item__num"><?= htmlspecialchars($plat['nom']) ?></span>
                                        <span class="commande-statut statut--prep"><?= $plat['categorie'] ?></span>
                                        <span class="commande-statut <?= $plat['actif'] ? 'statut--accepte' : 'statut--annule' ?>">
                                            <?= $plat['actif'] ? 'Actif' : 'Inactif' ?>
                                        </span>
                                    </div>
                                    <?php if ($plat['allergenes'] !== '—'): ?>
                                        <span class="employe-allergene">⚠ <?= $plat['allergenes'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="commande-item__actions">
                                    <button class="btn-compte-action btn-compte-action--modifier"
                                            onclick="toggleForm('form-plat-<?= $plat['plat_id'] ?>')">Modifier</button>
                                    <form method="POST" action="" style="display:inline;"
                                          onsubmit="return confirm('Supprimer ce plat ?')">
                                        <input type="hidden" name="action" value="delete_plat">
                                        <input type="hidden" name="plat_id" value="<?= $plat['plat_id'] ?>">
                                        <button type="submit" class="btn-compte-action btn-compte-action--annuler">Supprimer</button>
                                    </form>
                                </div>
                                <div class="commande-modif-form" id="form-plat-<?= $plat['plat_id'] ?>" style="display:none;">
                                    <form method="POST" action="" class="modif-form">
                                        <input type="hidden" name="action" value="update_plat">
                                        <input type="hidden" name="plat_id" value="<?= $plat['plat_id'] ?>">
                                        <div class="commande-field-row">
                                            <div class="commande-field">
                                                <label class="commande-label">Nom</label>
                                                <input type="text" name="nom" class="commande-input" value="<?= htmlspecialchars($plat['nom']) ?>">
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Catégorie</label>
                                                <select name="categorie" class="commande-input">
                                                    <?php foreach (['Entrée','Plat','Dessert','Boisson'] as $cat): ?>
                                                        <option <?= $plat['categorie'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="commande-field-row">
                                            <div class="commande-field">
                                                <label class="commande-label">Allergènes</label>
                                                <input type="text" name="allergenes" class="commande-input" value="<?= htmlspecialchars($plat['allergenes']) ?>">
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Statut</label>
                                                <select name="actif" class="commande-input">
                                                    <option value="1" <?= $plat['actif'] ? 'selected' : '' ?>>Actif</option>
                                                    <option value="0" <?= !$plat['actif'] ? 'selected' : '' ?>>Inactif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modif-form__actions">
                                            <button type="submit" class="btn btn-vg-primary">Enregistrer</button>
                                            <button type="button" class="btn btn-vg-secondary"
                                                    onclick="toggleForm('form-plat-<?= $plat['plat_id'] ?>')">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
 
            <!-- ====== ONGLET HORAIRES ====== -->
            <div class="compte-panel" id="tab-horaires">
                <div class="profil-card">
                    <form method="POST" action="" class="auth-form">
                        <input type="hidden" name="action" value="update_horaires">
                        <h3 class="employe-section-titre mb-3">Horaires d'ouverture</h3>
                        <div class="horaires-table">
                            <?php foreach ($horaires as $h): ?>
                                <div class="horaire-row">
                                    <div class="horaire-jour"><?= $h['jour'] ?></div>
                                    <div class="horaire-toggle">
                                        <label class="horaire-switch">
                                            <input type="checkbox" name="ouvert[<?= $h['jour'] ?>]"
                                                   <?= $h['ouvert'] ? 'checked' : '' ?>
                                                   onchange="toggleHoraire(this, '<?= $h['jour'] ?>')">
                                            <span class="horaire-switch__slider"></span>
                                        </label>
                                        <span class="horaire-toggle__label"><?= $h['ouvert'] ? 'Ouvert' : 'Fermé' ?></span>
                                    </div>
                                    <div class="horaire-heures" id="heures-<?= $h['jour'] ?>"
                                         style="<?= !$h['ouvert'] ? 'opacity:0.3; pointer-events:none;' : '' ?>">
                                        <input type="time" name="debut[<?= $h['jour'] ?>]"
                                               class="commande-input horaire-input"
                                               value="<?= date('H:i', strtotime($h['debut'])) ?>">
                                        <span class="horaire-sep">→</span>
                                        <input type="time" name="fin[<?= $h['jour'] ?>]"
                                               class="commande-input horaire-input"
                                               value="<?= date('H:i', strtotime($h['fin'])) ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="auth-btn mt-3">
                            Enregistrer les horaires
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                    </form>
                </div>
            </div>
 
        </div><!-- FIN TABS-CONTENT -->
    </div>
</section>
 
<script>

document.addEventListener('DOMContentLoaded', function () {

    // --- 1. Gestion des onglets principaux ---
    const tabs = document.querySelectorAll('.compte-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // Désactive tout
            tabs.forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.compte-panel').forEach(p => p.classList.remove('active'));
            
            // Active l'onglet cliqué et son panneau
            this.classList.add('active');
            const targetPanel = document.getElementById('tab-' + this.dataset.tab);
            if (targetPanel) targetPanel.classList.add('active');
        });
    });

    // --- 2. Gestion des sous-onglets (Menus/Plats) ---
    const sousTabs = document.querySelectorAll('.employe-sous-tab');
    
    sousTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            sousTabs.forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.employe-sous-panel').forEach(p => p.classList.remove('active'));
            
            this.classList.add('active');
            const targetSousPanel = document.getElementById(this.dataset.sous);
            if (targetSousPanel) targetSousPanel.classList.add('active');
        });
    });

    // --- 3. ACTIVATION PAR DÉFAUT AU CHARGEMENT ---
    // On cherche l'onglet qui a la classe "active" dans le HTML (ton onglet Commandes)
    const activeTab = document.querySelector('.compte-tab.active');
    if (activeTab) {
        const panelId = 'tab-' + activeTab.dataset.tab;
        const panel = document.getElementById(panelId);
        if (panel) panel.classList.add('active');
    }

});
 
// Toggle formulaires
function toggleForm(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
 
// Annulation employé
function ouvrirAnnulation (id) {
    const form = document.getElementById('annulation-' + id);
    if(form) {
        form.style.display = 'block';
    }
}

function fermerAnnulation (id) {
    const form = document.getElementById('annulation-' + id);
    if(form) {
        form.style.display = 'none';
    }
}
 
// Filtres commandes
function filtrerCommandes() {
    const statut = document.getElementById('filtre-statut').value.toLowerCase();
    const client = document.getElementById('filtre-client').value.toLowerCase();
    const items  = document.querySelectorAll('.employe-commande-item');
    let visible  = 0;
 
    items.forEach(item => {
        const matchStatut = !statut || item.dataset.statut === statut;
        const matchClient = !client || item.dataset.client.includes(client);
        const show = matchStatut && matchClient;
        item.style.display = show ? '' : 'none';
        if (show) visible++;
    });
 
    document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
}
 
// Toggle horaires
function toggleHoraire(checkbox, jour) {
    const container = document.getElementById('heures-' + jour);
    const label = checkbox.nextElementSibling;

    if(checkbox.checked) {
        container.style.opacity = "1";
        container.style.pointerEvents = "auto";
    } else {
        container.style.opacity = "O.3";
        container.style.pointerEvents = "none";
        label.textContent = "Fermé";
    }
}

const alerteSucces = document.querySelector('.auth-alert--success');
if(alerteSucces){
    setTimeout(() => {
        alerteSucces.style.transition = "opacity 0.5s ease";
        alerteSucces.style.opacity = "0";
        setTimeout(() => alerteSucces.remove(), 500);
    }, 3000);
}
</script>
 
<?php require_once '../includes/footer.php'; ?>
 