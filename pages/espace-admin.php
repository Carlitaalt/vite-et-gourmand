<?php
ob_start();
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';

exiger_role('administrateur');

$pageTitle = 'Espace Administrateur';
$rootPath = '../';
$curentPage = 'espace-admin';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$erreur = '';
$succes = '';


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'creer_employe':
            $prenom = trim($_POST['prenom']);
            $nom = trim($_POST['nom']);
            $email = trim($_POST['email']);
            $poste = trim($_POST['role']);
            $telephone = trim($_POST['telephone']);
            $adresse_postale = trim($_POST['adresse']);
            $ville = trim($_POST['ville']);
            $salaire = $_POST['salaire'] ?? 0;
            $mdp = password_hash($_POST['password'], PASSWORD_DEFAULT);

            try {
                $pdo->beginTransaction();
                //Insertion table utilisateur
                $stmt = $pdo->prepare("INSERT INTO utilisateur (prenom, nom, email, mot_de_passe, telephone, adresse_postale, ville, pays, role_id, actif, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'France', 2, 1, NOW(), NOW())");
                $stmt->execute([$prenom, $nom, $email, $mdp, $telephone, $adresse_postale, $ville]);

                $new_id = $pdo->lastInsertId();

                //Insertion table employes
                $stmt = $pdo->prepare("INSERT INTO employes (utilisateur_id, poste, salaire_horaire, date_embauche) VALUES (?, ?, ?, CURDATE())");
                $stmt->execute([$new_id, $poste, $salaire]);

                $pdo->commit();
                $succes = "Le compte employé de $prenom a été créé.";

            } catch (Exception $e) {
                if($pdo->inTransaction()) $pdo->rollBack();
                $erreur = "Erreur lors de la création : " . $e->getMessage();
            }
            break;

            case 'toggle_employe':
            $stmt = $pdo->prepare("UPDATE utilisateur SET actif = ? WHERE utilisateur_id = ?");
            $stmt->execute([$_POST['actif'], $_POST['employe_id']]);
            $succes = "Statut de l'employé mis à jour.";
            break;

            case 'modifier_employe':
            $emp_id = $_POST['employe_id'];
            $salaire = $_POST['salaire'];
            $poste = $_POST['poste'];
            $actif = isset($_POST['actif']) ? $_POST['actif'] : 1;

            try {
                $pdo->beginTransaction();
                //Update de l'activation dans utilisateur
                $stmt1 = $pdo->prepare("UPDATE utilisateur SET actif = ? WHERE utilisateur_id = ?");
                $stmt1->execute([$actif, $emp_id]);

                //Update du salaire et poste dans employes
                $stmt2 = $pdo->prepare("UPDATE employes SET salaire_horaire = ?, poste = ? WHERE utilisateur_id = ?");
                $stmt2->execute([$salaire, $poste, $emp_id]);

                $pdo->commit();
                $succes = "Fiche employé mise à jour.";
            } catch (Exception $e) {
                if($pdo->inTransaction()) $pdo->rollBack();
                $erreur = "Erreur de mise à jour: " . $e->getMessage();
            }
            break;

            case 'update_menu':
                $titre = trim($_POST['titre']);
                $prix = $_POST['prix'];
                $nb_pers_min = (int)($_POST['nombre_personne_minimum'] ?? 1);
                $description = trim($_POST['description'] ?? '');
                $conditions = trim($_POST['conditions'] ?? '');
                $actif = isset($_POST['actif']) ? $_POST['actif'] : 1;
                $theme_id = $_POST['theme_id'] ?? 1;
                $regime_id = $_POST['regime_id'] ?? 1;
                $menu_id = (!empty($_POST['menu_id'])) ? (int)$_POST['menu_id'] : null;
                
                try {
                    if($menu_id) {
                        $sql = "UPDATE menu SET titre = ?, prix_par_personne = ?, nombre_personne_minimum = ?, description = ?, conditions = ?, actif = ?, theme_id = ?, regime_id = ?, updated_at = NOW() WHERE menu_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$titre, $prix, $nb_pers_min, $description, $conditions, $actif, $theme_id, $regime_id, $menu_id]);
                        $succes = "Le menu '" . htmlspecialchars($titre) . "' a été mis à jour.";
                    } else {
                    //Création d'un nouveau menu
                        $sql = "INSERT INTO menu (titre, prix_par_personne, nombre_personne_minimum, description, conditions, actif, theme_id, regime_id, created_at, updated_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$titre, $prix, $nb_pers_min, $description, $conditions, $actif, $theme_id, $regime_id]);
                        $menu_id = $pdo->lastInsertId();
                        $succes = "Le nouveau menu a été crée.";

                    }
                
                if (isset($_FILES['menu_photo']) && $_FILES['menu_photo']['error'] === 0) {
                    $uploadDir = '../assets/images/menus/';

                    $extension = pathinfo($_FILES['menu_photo']['name'], PATHINFO_EXTENSION);
                    $nomFichier = "menu_" . $menu_id . "_" . time() . "." . $extension;
                    $destination = $uploadDir . $nomFichier;

                    if(move_uploaded_file($_FILES['menu_photo']['tmp_name'], $destination)) {
                        //Enregistrer le chemin de l'image en BDD
                        $urlBDD = "assets/images/menus/" . $nomFichier;

                        $stmtDel = $pdo->prepare("DELETE FROM menu_image WHERE menu_id = ?");
                        $stmtDel->execute([$menu_id]);

                        $stmtImg = $pdo->prepare("INSERT INTO menu_image (menu_id, url, ordre) VALUES (?, ?, 1)");
                        $stmtImg->execute([$menu_id, $urlBDD]);

                        $succes .= " Photo du menu enregistrée avec succès.";
                    }
                }
                } catch(PDOException $e){
                    $erreur = "Erreur SQL : " . $e->getMessage();
                }
                break;

            case 'delete_menu':
                $menu_id = (int)$_POST['menu_id'];
                try {
                    $pdo->beginTransaction();
                    $pdo->prepare("DELETE FROM menu_plat WHERE menu_id = ?")->execute([$menu_id]);
                    $pdo->prepare("DELETE FROM menu_image WHERE menu_id = ?")->execute([$menu_id]);
                    $pdo->prepare("DELETE FROM menu WHERE menu_id = ?")->execute([$menu_id]);

                    $pdo->commit();
                    $succes ="Menu supprimé avec succès.";
                } catch (Exception $e) {
                    if($pdo->inTransaction()) $pdo->rollBack();
                    $erreur = "Impossible de supprimer : ce menu est probablement lié à des commandes existantes.";
                }
                break;
                
            case 'update_plat':
                $titre = !empty($_POST['titre_plat']) ? trim($_POST['titre_plat']) : '';
                $desc = !empty($_POST['description']) ? trim($_POST['description']) : '';
                $cat = !empty($_POST['categorie']) ? trim($_POST['categorie']) : 'Plat';
                $menu_id = !empty($_POST['menu_id']) ? (int)$_POST['menu_id'] : null;
                $actif = isset($_POST['actif']) ? (int)$_POST['actif'] : 1;
                $plat_id = !empty($_POST['plat_id']) ? $_POST['plat_id'] : null;

                if(!$menu_id) {
                    $erreur = "Erreur : Vous devez rattacher le plat à un menu.";
                    break;
                }

                try {
                    $pdo->beginTransaction();

                    if($plat_id) {
                        //Modification
                        $sql = "UPDATE plat SET menu_id = ?, titre_plat = ?, description = ?, categorie = ?, actif = ? WHERE plat_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$menu_id, $titre, $desc, $cat, $actif, $plat_id]);

                        $pdo->prepare("DELETE FROM menu_plat WHERE plat_id = ?")->execute([$plat_id]);
                        $pdo->prepare("INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)")->execute([$menu_id, $plat_id]);

                        $succes = "Plat mis à jour !";
                    } else {
                        //création
                        $sql = "INSERT INTO plat (menu_id, titre_plat, description, categorie, actif) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$menu_id, $titre, $desc, $cat, $actif]);

                        $new_plat_id = $pdo->lastInsertId();

                        $sqlLien = "INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)";
                        $stmtLien = $pdo->prepare($sqlLien);
                        $stmtLien->execute([$menu_id, $new_plat_id]);

                        $succes = "Nouveau plat ajouté au menu !";
                    }
                    $pdo->commit();

                    header("Location: espace-admin.php?success=1");
                    exit;

                    } catch (PDOException $e) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                        $erreur = "Erreur Plat : " . $e->getMessage();
                    }
                    break;
                

            case 'delete_plat':
                $plat_id = $_POST['plat_id'];
                $stmt = $pdo->prepare("DELETE FROM plat WHERE plat_id = ?");
                $stmt->execute([$plat_id]);
                $succes = "Plat supprimé.";
                break;
                

            case 'update_horaires':
                try {
                    $pdo->beginTransaction();

                    foreach($_POST['debut'] as $jour => $heure_ouv) {
                        $heure_fer = $_POST['fin'][$jour];
                        $estOuvert = isset($_POST['ouvert'][$jour]);

                        if($estOuvert){
                            $final_ouv = !empty($heure_ouv) ? $heure_ouv : '09:00:00';
                            $final_fer = !empty($heure_fer) ? $heure_fer : '18:00:00';
                        } else {
                            $final_ouv = '00:00:00';
                            $final_fer = '00:00:00';
                        }

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

                case 'update_statut':
                    $commande_id = (int)$_POST['commande_id'];
                    $nouveau_statut = (int)$_POST['nouveau_statut'];

                    try {
                        $stmtCheck = $pdo->prepare("SELECT statut_id FROM commande WHERE commande_id = ?");
                        $stmtCheck->execute([$commande_id]);
                        $actuel = $stmtCheck->fetchColumn();

                        if($actuel != $nouveau_statut) {
                            $pdo->beginTransaction();

                            //MAJ du statut principal de la commande
                            $stmtUpdateCmd = $pdo->prepare("UPDATE commande SET statut_id = ? WHERE commande_id = ?");
                            $stmtUpdateCmd->execute([$nouveau_statut, $commande_id]);

                            //Ajout dans l'historique
                            $stmtInsertHist = $pdo->prepare("INSERT INTO commande_statut (commande_id, statut_id, date_modification) VALUES (?, ?, NOW())");
                            $stmtInsertHist->execute([$commande_id, $nouveau_statut]);

                            $pdo->commit();
                            $succes = "Statut de la commande mis à jour.";
                        } else {
                            $erreur = "Le nouveau statut est identique au statut actuel. Aucune modification. effectuée.";
                        }
                        
                    } catch (Exception $e) {
                        if($pdo->inTransaction()) $pdo->rollBack();
                        $erreur = "Erreur lors du changement de statut : " . $e->getMessage();
                    }
                    break;

                case 'annuler_commande':
                    $commande_id = (int)$_POST['commande_id'];
                    $motif = !empty($_POST['motif_annulation']) ? trim($_POST['motif_annulation']) : 'Annulation client';
                    $mode_contact = !empty($_POST['mode_contact']) ? $_POST['mode_contact'] : 'Email';
                    $statut_annule = 8;

                    try {
                        $pdo->beginTransaction();
                        //MAJ du statut de la commande
                        $stmtAnnule = $pdo->prepare("UPDATE commande SET
                            statut_id = ?,
                            motif_annulation = ?,
                            mode_contact = ?
                            WHERE commande_id = ?");
                        $stmtAnnule->execute([$statut_annule, $motif, $mode_contact, $commande_id]);

                        $stmtInsertHist = $pdo->prepare("INSERT INTO commande_statut (commande_id, statut_id, date_modification) VALUES (?, ?, NOW())");
                        $stmtInsertHist->execute([$commande_id, $statut_annule]);

                        $pdo->commit();
                        $succes = "La commande a été annulée.";
                    } catch(Exception $e) {
                        if($pdo->inTransaction()) $pdo->rollBack();
                        $erreur = "Erreur lors de l'annulation : " . $e->getMessage();
                    }
                    break;

                    case 'valider_avis':
                        $avis_id = (int)$_POST['avis_id'];
                        $stmt = $pdo->prepare("UPDATE avis SET statut_avis_id = 2 WHERE avis_id = ?");
                        $stmt->execute([$avis_id]);
                        $succes = "L'avis a été publié.";
                        break;

                    case 'refuser_avis':
                        $avis_id = (int)$_POST['avis_id'];
                        $stmt = $pdo->prepare("UPDATE avis SET statut_avis_id = 3 WHERE avis_id = ?");
                        $stmt->execute([$avis_id]);
                        $succes = "L'avis a été refusé.";
                        break;

                    default:
                        $erreur = "Action inconnue : " . $_POST['action'];
                        break;
    }
}
 
//Récupération menus BDD
$stmtM = $pdo->query("SELECT * FROM menu ORDER BY titre ASC");
$menusBDD = $stmtM->fetchAll(PDO::FETCH_ASSOC);

//Récupération des horaires
$stmtH = $pdo->query("SELECT * FROM horaire ORDER BY horaire_id ASC");
$horairesData = $stmtH->fetchAll(PDO::FETCH_ASSOC);
$horaires = [];
foreach($horairesData as $r){
    $horaires[] = [
        'jour' => $r['jour'],
        'ouvert' => ($r['heure_ouverture'] !== '00:00:00'),
        'debut' => $r['heure_ouverture'],
        'fin' => $r['heure_fermeture']
    ];
}

//Employés
$stmtE = $pdo->query("
        SELECT u.utilisateur_id as employe_id, u.prenom, u.nom, u.email, u.telephone, e.poste as role, e.salaire_horaire, u.actif, u.created_at, u.updated_at
        FROM utilisateur u
        INNER JOIN employes e ON u.utilisateur_id = e.utilisateur_id
        WHERE u.role_id = 2
        ");
$employes = $stmtE->fetchAll(PDO::FETCH_ASSOC);

//Info admin connecté
$stmtAdmin = $pdo->prepare("
    SELECT u.prenom, u.nom, u.email, r.libelle as role
    FROM utilisateur u
    JOIN role r ON u.role_id = r.role_id
    WHERE u.utilisateur_id = ? AND u.role_id = 3
");
$stmtAdmin->execute([$_SESSION['user_id'] ?? 0]);
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

if(!$admin) {
    $admin = [
        'prenom' => 'Admin',
        'nom' => '',
        'email' => '',
        'role' => 'Administrateur'];
}
//Commandes
try {
    $sqlCommandes ="SELECT c.*,
                    u.prenom AS client_prenom,
                    u.nom AS client_nom,
                    u.email AS client_email,
                    u.telephone AS client_telephone,
                    m.titre AS menu_titre,
                    c.nombre_personnes,
                    c.heure_livraison,
                    c.date_prestation
                    FROM commande c
                    INNER JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
                    INNER JOIN commande_menu cm ON c.commande_id = cm.commande_id
                    INNER JOIN menu m ON cm.menu_id = m.menu_id
                    GROUP BY c.commande_id
                    ORDER BY c.date_commande DESC";
    $commandes = $pdo->query($sqlCommandes)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $commandes = [];
    $erreur = "Erreur commandes : " . $e->getMessage();
}

//Calcul des stats

$caTotal = 0;
$statsParMenu = [];
$ID_STATUT_TERMINE = 7;

foreach($commandes as &$cmd){
    $titre = $cmd['menu_titre'] ?? 'Menu inconnu';

    if(!isset($statsParMenu[$titre])){
        $statsParMenu[$titre] = ['nb_commandes' => 0, 'ca' => 0.0];
    }

    $statsParMenu[$titre]['nb_commandes']++;

    if($cmd['statut_id'] == $ID_STATUT_TERMINE){
            $caTotal += $cmd['prix_total'];
            $statsParMenu[$titre]['ca'] += $cmd['prix_total'];
    }
    $stmtHistorique = $pdo->prepare("
            SELECT cs.statut_id as statut, cs.date_modification as date, 'Admin' as auteur
            FROM commande_statut cs
            WHERE cs.commande_id = ?
            ORDER BY cs.date_modification ASC
            " );
            $stmtHistorique->execute([$cmd['commande_id']]);
            $cmd['$historique'] = $stmtHistorique->fetchAll(PDO::FETCH_ASSOC);
}
unset($cmd);


//Compteurs pour les widgets
$nbEnAttente = count(array_filter($commandes, fn($c) => $c['statut_id'] == 1));
$nbEnCours = count(array_filter($commandes, fn($c) => in_array($c['statut_id'], [2, 3, 4, 5, 6])));
$nbEmployes = count(array_filter($employes, fn($e) => $e['actif']));
$nbAvisAttente = 0;

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

// Avis
try {
    $stmtAvis = $pdo->query("
        SELECT a.avis_id, a.note, a.description as commentaire, a.created_at as date,
                a.statut_avis_id,
                sa.libelle as statut_libelle,
                u.prenom AS client,
                u.nom AS client_nom,
                m.titre AS menu_titre
        FROM avis a
        JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
        JOIN commande c ON a.commande_id = c.commande_id
        JOIN commande_menu cm ON c.commande_id = cm.commande_id
        JOIN menu m ON cm.menu_id = m.menu_id
        JOIN statut_avis sa ON a.statut_avis_id = sa.statut_avis_id
        ORDER BY (a.statut_avis_id = 1) DESC, a.created_at DESC
    ");
    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);

    //On met a jour le compteur pour les stats
    $nbAvisAttente = count(array_filter($avis, fn($a) => $a['statut_avis_id'] === 1));
} catch (PDOException $e) {
    $avis = [];
}

// Menus complets
try {
    $stmtMenus = $pdo->query("
        SELECT m.*, COUNT(mp.plat_id) as nb_plats,
               mi.url as image_url
        FROM menu m
        LEFT JOIN menu_plat mp ON m.menu_id = mp.menu_id
        LEFT JOIN menu_image mi ON m.menu_id = mi.menu_id AND mi.ordre = 1
        GROUP BY m.menu_id
        ORDER BY m.titre ASC
    ");
    $menus = $stmtMenus->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $menus = [];
}

// Plats
try {
    $stmtPlats = $pdo->query("
        SELECT p.plat_id, p.titre_plat, p.description, p.categorie, p.actif,
                mp.menu_id,
                m.titre AS menu_titre,
               GROUP_CONCAT(a.libelle SEPARATOR ', ') as allergenes
        FROM plat p
        LEFT JOIN menu_plat mp ON p.plat_id = mp.plat_id
        LEFT JOIN menu m ON mp.menu_id = m.menu_id
        LEFT JOIN plat_allergene pa ON p.plat_id = pa.plat_id
        LEFT JOIN allergene a ON pa.allergene_id = a.allergene_id
        GROUP BY p.plat_id
        ORDER BY p.titre_plat ASC
    ");
    $plats = $stmtPlats->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $plats = [];
}

$themes = $pdo->query("SELECT * FROM theme ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
$regimes = $pdo->query("SELECT * FROM regime ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="section-compte section-employe section-admin">
    <div class="auth-bg-deco"></div>
    <div class="container">

    <!-- En tête admin -->
     <div class="compte-header">
        <div class="compte-avatar admin-avatar">
            <?= strtoupper(substr($admin['prenom'], 0, 1) . substr($admin['nom'], 0, 1)) ?>
        </div>
        <div class="compte-header__info">
            <h1 class="compte-header__nom">
                <?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?>
                <span class="employe-role-badge admin-role-badge"><?= htmlspecialchars($admin['role']) ?></span>
            </h1>
            <p class="compte-header__email"><?= htmlspecialchars($admin['email']) ?></p>
        </div>
     </div>


    <!-- Stats rapides -->
    <div class="employe-stats">
        <div class="employe-stat-card employe-stat-card--attente">
            <div class="employe-stat__val"><?= $nbEnAttente ?></div>
            <div class="employe-stat__label">En attente</div>
        </div>
        <div class="employe-stat-card employe-stat-card--cours">
            <div class="employe-stat__val"><?= $nbEnCours ?></div>
            <div class="employe-stat__label">En cours</div>
        </div>
        <div class="employe-stat-card employe-stat-card--avis">
            <div class="employe-stat__val"><?= $nbAvisAttente ?></div>
            <div class="employe-stat__label">Avis à valider</div>
        </div>
        <div class="employe-stat-card employe-stat-card--ca">
            <div class="employe-stat__val"><?= number_format($caTotal, 0, ',', ' ') ?> €</div>
            <div class="employe-stat__label">CA total</div>
        </div>
        <div class="employe-stat-card employe-stat-card--employes">
            <div class="employe-stat__val"><?= $nbEmployes ?></div>
            <div class="employe-stat__label">Employés actifs</div>
        </div>
    </div>

    <!-- Alertes -->
    <?php if ($succes): ?>
        <div class="auth-alert auth-alert--success mb-3">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($succes) ?>
        </div>
    <?php endif; ?>
 
    <!-- Onglets -->
    <div class="compte-tabs">
        <button class="compte-tab active" data-tab="employes">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Employés
            <span class="compte-tab__badge admin-badge"><?= count($employes) ?></span>
        </button>
        <button class="compte-tab" data-tab="statistiques">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Statistiques & CA
        </button>
        <button class="compte-tab" data-tab="commandes">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Commandes
            <?php if ($nbEnAttente > 0): ?>
                <span class="compte-tab__badge"><?= $nbEnAttente ?></span>
            <?php endif; ?>
        </button>
        <button class="compte-tab" data-tab="avis-employe">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Avis clients
            <?php if ($nbAvisAttente > 0): ?>
                <span class="compte-tab__badge"><?= $nbAvisAttente ?></span>
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
 
    <!-- ====== ONGLET EMPLOYÉS ====== -->
    <div class="compte-panel active" id="tab-employes">
 
        <!-- Créer un employé -->
        <div class="admin-section-header">
            <h3 class="employe-section-titre">Gestion des comptes employés</h3>
            <button class="btn-compte-action btn-compte-action--modifier"
                onclick="toggleForm('form-nouvel-employe')">
                + Créer un compte employé
            </button>
        </div>
 
    <!-- Formulaire création employé -->
    <div class="commande-modif-form admin-form-employe" id="form-nouvel-employe" style="display:none;">
        <form method="POST" action="" class="modif-form">
            <input type="hidden" name="action" value="creer_employe">
            <h4 class="modif-form__titre">Nouveau compte employé</h4>
            <div class="admin-notice">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                L'employé recevra un e-mail l'informant de la création de son compte. <strong>Le mot de passe ne sera pas communiqué par mail</strong> — il devra vous contacter directement pour l'obtenir.
            </div>
            <div class="commande-field-row">
                <div class="commande-field">
                    <label class="commande-label">Prénom *</label>
                    <input type="text" name="prenom" class="commande-input" placeholder="Prénom" required>
                </div>
                <div class="commande-field">
                    <label class="commande-label">Nom *</label>
                    <input type="text" name="nom" class="commande-input" placeholder="Nom" required>
                </div>
            </div>
            <div class="commande-field-row">
                <div class="commande-field">
                    <label class="commande-label">Adresse e-mail (identifiant) *</label>
                    <input type="email" name="email" class="commande-input" placeholder="prenom.nom@juliescatering.fr" required>
                </div>
                <div class="commande-field">
                    <label class="commande-label">Rôle</label>
                    <input type="text" name="role" class="commande-input" placeholder="Ex : Chef de cuisine">
                </div>
            </div>
            <div class="commande-field-row">
                <div class="commande-field">
                    <label class="commande-label">Téléphone</label>
                    <input type="text" name="telephone" placeholder="06 00 00 00 00" class="commande-input">
                </div>
                <div class="commande-field">
                    <label class="commande-label">Adresse Postal</label>
                    <input type="text" name="adresse" placeholder="12 rue des Lilas" class="commande-input">
                </div>
            </div>
            <div class="commande-field-row">
                <div class="commande-field">
                    <label class="commande-label">Ville</label>
                    <input type="text" name="ville" placeholder="Bordeaux" class="commande-input">
                </div>
                <div class="commande-field">
                    <label class="commande-label">Salaire horaire</label>
                    <input type="number" step="0.01" name="salaire" placeholder="Salaire horaire (ex: 13.50)" class="commande-input">
                </div>
            </div>
            <div class="commande-field-row">
                <div class="commande-field">
                    <label class="commande-label">Mot de passe *</label>
                    <input type="password" name="password" class="commande-input" placeholder="••••••••" required>
                </div>
                <div class="commande-field">
                    <label class="commande-label">Confirmer le mot de passe *</label>
                    <input type="password" name="password_confirm" class="commande-input" placeholder="••••••••" required>
                </div>
            </div>
                <div class="modif-form__actions">
                    <button type="submit" class="btn btn-vg-primary">Créer le compte</button>
                    <button type="button" class="btn btn-vg-secondary"
                    onclick="toggleForm('form-nouvel-employe')">Annuler</button>
                </div>
            </form>
        </div>
 
        <!-- Liste employés -->
        <div class="admin-employes-liste">
            <?php foreach ($employes as $emp): ?>
            <div class="admin-employe-card <?= !$emp['actif'] ? 'admin-employe-card--inactif' : '' ?>">
                <div class="admin-employe-card__left">
                    <div class="employe-client-avatar <?= !$emp['actif'] ? 'avatar--inactif' : '' ?>">
                        <?= strtoupper(substr($emp['prenom'], 0, 1) . substr($emp['nom'], 0, 1)) ?>
                    </div>
                <div class="admin-employe-info-wrap">
                    <div class="admin-employe-nom">
                        <?= htmlspecialchars($emp['prenom'] . ' ' . $emp['nom']) ?>
                        <span class="commande-statut <?= $emp['actif'] ? 'statut--accepte' : 'statut--annule' ?>">
                            <?= $emp['actif'] ? 'Actif' : 'Désactivé' ?>
                        </span>
                    </div>
                    <div class="admin-employe-meta">
                        <span><?= htmlspecialchars($emp['email']) ?></span>
                        <span>·</span>
                        <span><?= htmlspecialchars($emp['role']) ?></span>
                    </div>
                    <div class="admin-employe-dates">
                        Membre depuis le <?= date('d/m/Y', strtotime($emp['created_at'])) ?>
                        · Dernière modif : <?= date('d/m/Y à H:i', strtotime($emp['updated_at'])) ?>
                    </div>
                </div>
            </div>
            <div class="admin-employe-card__actions">
                <button type="button" class="btn-compte-action btn-compte-action--modifier" onclick="document.getElementById('edit-form-<?= $emp['employe_id'] ?>').style.display='flex'">
                    ✎ Modifier
                </button>

                <form method="POST" action="" style="display:inline;"
                    onsubmit="return confirm('<?= $emp['actif'] ? 'Désactiver' : 'Réactiver' ?> ce compte employé ?')">
                    <input type="hidden" name="action" value="toggle_employe">
                    <input type="hidden" name="employe_id" value="<?= $emp['employe_id'] ?>">
                    <input type="hidden" name="actif" value="<?= $emp['actif'] ? '0' : '1' ?>">
                    <button type="submit" class="btn-compte-action <?= $emp['actif'] ? 'btn-compte-action--annuler' : 'btn-compte-action--modifier' ?>">
                        <?= $emp['actif'] ? '✕ Désactiver' : '✓ Réactiver' ?>
                    </button>
                </form>

                <div id="edit-form-<?= $emp['employe_id'] ?>" class="admin-modal-overlay" style="display:none;">
                    <div class="admin-modal-content">
                        <form method="POST">
                            <input type="hidden" name="action" value="modifier_employe">
                            <input type="hidden" name="employe_id" value="<?= $emp['employe_id'] ?>">
                            <h3>Modifier la fiche de <?= htmlspecialchars($emp['prenom']) ?></h3>

                            <div class="commande-field">
                                <label class="commande-label">Poste actuel</label>
                                <input type="text" name="poste" value="<?= htmlspecialchars($emp['role']) ?>" class="commande-input" required>
                            </div>

                            <div class="commande-field">
                                <label class="commande-label">Salaire horaire (€)</label>
                                <input type="number" step="0.01" name="salaire" value="<?= $emp['salaire_horaire'] ?>" class="commande-input" required>
                            </div>

                            <div class="modif-form__actions">
                                <button type="submit" class="btn btn-vg-primary">Enregistrer les modifications</button>
                                <button type="button" class="btn btn-vg-secondary" onclick="document.getElementById('edit-form-<?= $emp['employe_id'] ?>').style.display='none';">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
 
            <!-- ====== ONGLET STATISTIQUES & CA ====== -->
            <div class="compte-panel" id="tab-statistiques">
 
                <!-- Filtres CA -->
                <div class="admin-stats-filtres">
                    <div class="employe-filtre-group">
                        <label class="employe-filtre-label">Filtrer par menu</label>
                        <select class="commande-input" id="filtre-menu-ca" onchange="mettreAJourCA()">
                            <option value="">Tous les menus</option>
                            <?php foreach ($menusBDD as $m): ?>
                                <option value="<?= htmlspecialchars($m['titre']) ?>">
                                    <?= htmlspecialchars($m['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="employe-filtre-group">
                        <label class="employe-filtre-label">Date de début</label>
                        <input type="date" class="commande-input" id="filtre-date-debut" onchange="mettreAJourCA()">
                    </div>
                    <div class="employe-filtre-group">
                        <label class="employe-filtre-label">Date de fin</label>
                        <input type="date" class="commande-input" id="filtre-date-fin" onchange="mettreAJourCA()">
                    </div>
                    <div class="employe-filtre-group" style="align-self:flex-end;">
                        <button class="btn-compte-action btn-compte-action--modifier" onclick="reinitialiserFiltres()">
                            Réinitialiser
                        </button>
                    </div>
                </div>
 
                <!-- Résultat CA filtré -->
                <div class="admin-ca-resultat">
                    <div class="admin-ca-card">
                        <div class="admin-ca-card__label">Chiffre d'affaires filtré</div>
                        <div class="admin-ca-card__val" id="ca-filtre-val"><?= number_format($caTotal, 2, ',', ' ') ?> €</div>
                        <div class="admin-ca-card__sub" id="ca-filtre-nb"><?= count(array_filter($commandes, fn($c) => $c['statut_id'] === 7)) ?> commandes terminées</div>
                    </div>
                </div>
 
                <!-- Graphique commandes par menu -->
                <div class="commande-bloc">
                    <div class="bloc-header" style="padding:1rem 1.25rem; border-bottom:1px solid var(--creme-fonce);">
                        <span class="employe-section-titre" style="margin:0;">Commandes par menu</span>
                        <div class="admin-chart-toggle">
                            <button class="admin-chart-btn active" onclick="changerGraphique('barres', this)">Barres</button>
                            <button class="admin-chart-btn" onclick="changerGraphique('camembert', this)">Camembert</button>
                        </div>
                    </div>
                    <div style="padding:1.5rem;">
                        <canvas id="graphiqueMenus" height="300"></canvas>
                    </div>
                </div>
 
                <!-- Tableau récap par menu -->
                <div class="commande-bloc" style="margin-top:1.5rem;">
                    <div class="bloc-header" style="padding:1rem 1.25rem; border-bottom:1px solid var(--creme-fonce);">
                        <span class="employe-section-titre" style="margin:0;">Récapitulatif par menu</span>
                    </div>
                    <div style="padding:0;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Nb commandes</th>
                                    <th>CA généré</th>
                                    <th>Part du CA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statsParMenu as $titre => $stats): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($titre) ?></td>
                                        <td><?= $stats['nb_commandes'] ?></td>
                                        <td><?= number_format($stats['ca'], 2, ',', ' ') ?> €</td>
                                        <td>
                                            <?php $part = $caTotal > 0 ? round($stats['ca'] / $caTotal * 100) : 0; ?>
                                            <div class="admin-progress-container">
                                                <div class="admin-progress-bar">
                                                    <div class="admin-progress-bar__fill" style="width:<?= $part ?>%"></div>
                                                </div>
                                                <span><?= $part ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
 
            <!-- ====== ONGLET COMMANDES (identique employé) ====== -->
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
                                            onclick="ouvrirAnnulationAdmin(<?= $cmd['commande_id'] ?>)">
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
                                                onclick="fermerAnnulationAdmin(<?= $cmd['commande_id'] ?>)">Retour</button>
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
                        <span class="commande-info-label" style="font-style: italic;">
                            Traité le <?= date('d/m à H:i', strtotime($a['date'])) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="compte-empty"><p>Aucun avis dans l'historique.</p></div>
        <?php endif; ?>
    </div>
</div>
            <!-- ====== ONGLET MENUS & PLATS ====== -->
            <div class="compte-panel" id="tab-menus">
                <div class="employe-sous-tabs">
                    <button class="employe-sous-tab active" data-sous="admin-sous-menus">Menus</button>
                    <button class="employe-sous-tab" data-sous="admin-sous-plats">Plats</button>
                </div>
 
                <div class="employe-sous-panel active" id="admin-sous-menus">
                    <div class="employe-catalogue-header">
                        <h3 class="employe-section-titre">Gestion des menus</h3>
                        <button class="btn-compte-action btn-compte-action--modifier"
                                onclick="toggleForm('form-admin-nouveau-menu')">+ Nouveau menu</button>
                    </div>
                    <div class="commande-modif-form" id="form-admin-nouveau-menu" style="display:none;">
                        <form method="POST" action="" class="modif-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_menu">
                            <h4 class="modif-form__titre">Nouveau menu</h4>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label class="commande-label">Photo du menu</label>
                                    <input type="file" name="menu_photo" class="commande-input" accept="image/*">
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Titre</label>
                                    <input type="text" name="titre" class="commande-input" required>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Description</label>
                                    <textarea name="description" class="commande-input avis-textarea" placeholder="Description du menu..." rows="3"></textarea>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Conditions</label>
                                    <textarea name="conditions" id="conditions" class="commande-input avis-textarea" rows="3" placeholder="Ex : À commander 5 jours avant, minimum 10 personnes..."></textarea>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Thème</label>
                                    <select name="theme_id" class="commande-input">
                                        <?php foreach($themes as $t): ?>
                                            <option value="<?= $t['theme_id'] ?>"><?= htmlspecialchars($t['libelle']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Régime</label>
                                    <select name="regime_id" class="commande-input">
                                        <?php foreach($regimes as $r): ?>
                                            <option value="<?= $r['regime_id'] ?>"><?= htmlspecialchars($r['libelle']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Prix / pers. (€)</label>
                                    <input type="number" name="prix" class="commande-input" step="0.50" min="0" required>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Nb. personnes min.</label>
                                    <input type="number" name="nombre_personne_minimum" class="commande-input" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="modif-form__actions">
                                <button type="submit" class="btn btn-vg-primary">Créer</button>
                                <button type="button" class="btn btn-vg-secondary"
                                        onclick="toggleForm('form-admin-nouveau-menu')">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
                    <div class="employe-catalogue">
                        <?php foreach ($menus as $menu): ?>
                            <div class="employe-catalogue-item <?= !$menu['actif'] ? 'item--inactif' : '' ?>">
                                <div class="employe-catalogue-item__header">
                                    <div class="employe-catalogue-item__main">
                                        <?php if (!empty($menu['image_url'])): ?>
                                            <img src="../<?= htmlspecialchars($menu['image_url']) ?>" alt="<?= htmlspecialchars($menu['titre']) ?>" class="menu-vignette-admin">
                                        <?php else: ?>
                                            <div class="menu-vignette-placeholder">Aucune image</div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <span class="commande-item__num"><?= htmlspecialchars($menu['titre']) ?></span>
                                            <span class="commande-statut <?= $menu['actif'] ? 'statut--accepte' : 'statut--annule' ?>">
                                                <?= $menu['actif'] ? 'Actif' : 'Inactif' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="employe-catalogue-item__meta">
                                        <span><?= $menu['nb_plats'] ?> plats</span>
                                        <span class="commande-item__prix"><?= number_format($menu['prix_par_personne'], 2, ',', ' ') ?> € / pers.</span>
                                    </div>
                                </div>
                                <div class="commande-item__actions">
                                    <button class="btn-compte-action btn-compte-action--modifier"
                                            onclick="toggleForm('form-admin-menu-<?= $menu['menu_id'] ?>')">Modifier</button>
                                    <form method="POST" action="" style="display:inline;"
                                          onsubmit="return confirm('Supprimer ce menu ?')">
                                        <input type="hidden" name="action" value="delete_menu">
                                        <input type="hidden" name="menu_id" value="<?= $menu['menu_id'] ?>">
                                        <button type="submit" class="btn-compte-action btn-compte-action--annuler">Supprimer</button>
                                    </form>
                                </div>
                                <div class="commande-modif-form" id="form-admin-menu-<?= $menu['menu_id'] ?>" style="display:none;">
                                    <form method="POST" action="" class="modif-form" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="update_menu">
                                        <input type="hidden" name="menu_id" value="<?= $menu['menu_id'] ?>">
                                        <div class="commande-field-row">
                                            <div class="commande-field">
                                                <label class="commande-label">Changer la photo</label>
                                                <div style="display:flex; flex-direction:column; gap:5px;">
                                                    <input type="file" name="menu_photo" class="commande-input" accept="image/*">
                                                    <?php if(!empty($menu['image_url'])): ?>
                                                        <span style="font-size: 0.70em; color: #c4973a">
                                                            Fichier actuel : <?= basename($menu['image_url']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="font-size:0.70em; color: #999;">
                                                            Aucune photo enregistrée
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Titre</label>
                                                <input type="text" name="titre" class="commande-input" value="<?= htmlspecialchars($menu['titre']) ?>">
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Description</label>
                                                <textarea name="description" class="commande-input avis-textarea" placeholder="Description du menu..." rows="3"><?= htmlspecialchars($menu['description'] ?? "") ?></textarea>
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Conditions</label>
                                                <textarea name="conditions" class="commande-input avis-textarea" placeholder="Ex : À commander 5 jours avant, minimum 10 personnes..."><?= htmlspecialchars($menu['conditions'] ?? "") ?></textarea>
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Nb. personnes min.</label>
                                                <input type="number" name="nombre_personne_minimum" class="commande-input" min="1" value="<?= $menu['nombre_personne_minimum'] ?? 1 ?>" required>
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Thème</label>
                                                <select name="theme_id" class="commande-input">
                                                    <?php foreach($themes as $t): ?>
                                                        <option value="<?= $t['theme_id'] ?>" <?= $t['theme_id'] == $menu['theme_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['libelle']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Régime</label>
                                                <select name="regime_id" class="commande-input">
                                                    <?php foreach($regimes as $r): ?>
                                                        <option value="<?= $r['regime_id'] ?>" <?= $r['regime_id'] == $menu['regime_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($r['libelle']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
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
                                                    onclick="toggleForm('form-admin-menu-<?= $menu['menu_id'] ?>')">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
 
                <div class="employe-sous-panel" id="admin-sous-plats">
                    <div class="employe-catalogue-header">
                        <h3 class="employe-section-titre">Gestion des plats</h3>
                        <button class="btn-compte-action btn-compte-action--modifier"
                                onclick="toggleForm('form-admin-nouveau-plat')">+ Nouveau plat</button>
                    </div>
                    <div class="admin-search-bar" style="margin:15px 0; display:flex; gap:10px;">
                        <input type="text" id="filterPlatName" class="commande-input" placeholder="Rechercher un plat..." onkeyup="filterPlats()">

                        <select id="filterPlatMenu" class="commande-input" onchange="filterPlats()">
                            <option value="">Tous les menus</option>
                            <?php foreach($menusBDD as $m): ?>
                                <option value="<?= htmlspecialchars($m['titre']) ?>"><?= htmlspecialchars($m['titre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                     <div class="compte-empty" id="no-results-plats" style="display:none;">
                        <h3>Aucun plat trouvé</h3>
                        <p>Modifiez vos filtres pour voir d'autres plats.</p>
                    </div>

                    <div class="commande-modif-form" id="form-admin-nouveau-plat" style="display:none;">
                        <form method="POST" action="" class="modif-form">
                            <input type="hidden" name="action" value="update_plat">

                            <div class="commande-field">
                                <label class="commande-label">Rattacher à quel Menu</label>
                                <select name="menu_id" class="commande-input" required>
                                    <?php foreach($menusBDD as $m): ?>
                                        <option value="<?= $m['menu_id'] ?>"><?= htmlspecialchars($m['titre']) ?></option>
                                        <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label class="commande-label">Nom du plat</label>
                                    <input type="text" name="titre_plat" class="commande-input" required>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Catégorie</label>
                                    <select name="categorie" class="commande-input">
                                        <option value="Entrée">Entrée</option>
                                        <option value="Plat">Plat principal</option>
                                        <option value="Dessert">Dessert</option>
                                        <option value="Boisson">Boisson</option>
                                    </select>
                                </div>
                            </div>

                            <div class="commande-field">
                                <label class="commande-label">Description / Allergènes</label>
                                <textarea name="description" class="commande-input"></textarea>
                            </div>

                            <div class="modif-form__actions">
                                <button type="submit" class="btn btn-vg-primary">Ajouter le plat</button>
                                <button type="button" class="btn btn-vg-secondary"
                                        onclick="toggleForm('form-admin-nouveau-plat')">Annuler</button>
                            </div>
                        </form>
                    </div>

                    <div class="employe-catalogue">
                        <?php foreach ($plats as $plat): ?>
                            <div class="employe-catalogue-item <?= !$plat['actif'] ? 'item--inactif' : '' ?>">
                                <div class="employe-catalogue-item__header">
                                    <div>
                                        <span class="commande-item__num"><?= htmlspecialchars($plat['titre_plat']) ?></span>
                                        <span class="commande-statut <?= $plat['actif'] ? 'statut--accepte' : 'statut--annule' ?>">
                                            <?= $plat['actif'] ? 'Actif' : 'Inactif' ?>
                                        </span>
                                        <span class="commande-statut statut--prep"><?= htmlspecialchars($plat['categorie']) ?></span>
                                        <small>Menu rattaché : <strong><?= htmlspecialchars($plat['menu_titre'] ?? 'Aucun') ?></strong></small>
                                    </div>
                                </div>

                                <div class="commande-item__actions">
                                    <button class="btn-compte-action btn-compte-action--modifier"
                                            onclick="toggleForm('form-admin-plat-<?= $plat['plat_id'] ?>')">Modifier</button>

                                    <form method="POST" action="" style="display:inline;"
                                          onsubmit="return confirm('Supprimer ce plat ?')">
                                        <input type="hidden" name="action" value="delete_plat">
                                        <input type="hidden" name="plat_id" value="<?= $plat['plat_id'] ?>">
                                        <button type="submit" class="btn-compte-action btn-compte-action--annuler">Supprimer</button>
                                    </form>
                                </div>

                                <div class="commande-modif-form" id="form-admin-plat-<?= $plat['plat_id'] ?>" style="display:none;">
                                    <form method="POST" action="" class="modif-form">
                                        <input type="hidden" name="action" value="update_plat">
                                        <input type="hidden" name="plat_id" value="<?= $plat['plat_id'] ?>">

                                        <div class="commande-field">
                                            <label class="commande-label">Changer de Menu</label>
                                            <select name="menu_id" class="commande_input">
                                                <?php foreach ($menusBDD as $m): ?>
                                                    <option value="<?= $m['menu_id'] ?>" <?= $m['menu_id'] == $plat['menu_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($m['titre']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="commande-field-row">
                                            <div class="commande-field">
                                                <label class="commande-label">Nom du plat</label>
                                                <input type="text" name="titre_plat" class="commande-input" value="<?= htmlspecialchars($plat['titre_plat']) ?>">
                                            </div>
                                            <div class="commande-field">
                                                <label class="commande-label">Catégorie</label>
                                                <select name="categorie" class="commande-input">
                                                    <option value="Entrée" <?= $plat['categorie'] == 'Entrée' ? 'selected' : '' ?>>Entrée</option>
                                                    <option value="Plat" <?= $plat['categorie'] == 'Plat' ? 'selected' : '' ?>>Plat</option>
                                                    <option value="Dessert" <?= $plat['categorie'] == 'Dessert' ? 'selected' : '' ?>>Dessert</option>
                                                    <option value="Boisson" <?= $plat['categorie'] == 'Boisson' ? 'selected' : '' ?>>Boisson</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="commande-field">
                                            <label class="commande-label">Description</label>
                                            <textarea name="description" class="commande-input"><?= htmlspecialchars($plat['description']) ?></textarea>
                                        </div>

                                        <div class="commande-field">
                                            <label class="commande-label">Statut</label>
                                            <select name="actif" class="commande-input">
                                                <option value="1" <?= $plat['actif'] ? 'selected' : '' ?>>Actif</option>
                                                <option value="0" <?= !$plat['actif'] ? 'selected' : '' ?>>Inactif</option>
                                            </select>
                                        </div>

                                        <div class="modif-form__actions">
                                            <button type="submit" class="btn btn-vg-primary">Enregistrer</button>
                                            <button type="button" class="btn btn-vg-secondary" onclick="toggleForm('form-admin-plat-<?= $plat['plat_id'] ?>')">Annuler</button>
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
                                               class="commande-input horaire-input" value="<?= date('H:i', strtotime($h['debut'])) ?>">
                                        <span class="horaire-sep">→</span>
                                        <input type="time" name="fin[<?= $h['jour'] ?>]"
                                               class="commande-input horaire-input" value="<?= date('H:i', strtotime($h['fin'])) ?>">
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
 
<!-- Chart.js via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
 
<script>

 //Disparition automatique des alertes après 5 secondes
const alerts = document.querySelectorAll('.auth-alert');
alerts.forEach(alert => {
    setTimeout(() => {
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    }, 5000);
});


// Données pour le graphique (injectées depuis PHP)
// --- DEBUT DU BLOC SECURISE ---
const donneesMenus = <?php
    if (!empty($statsParMenu)) {
        $cleanStats = [];
        foreach ($statsParMenu as $titre => $stats) {
            $cleanStats[] = [
                'titre'        => $titre,
                'nb_commandes' => $stats['nb_commandes'],
                'ca'           => $stats['ca']
            ];
        }
        echo json_encode($cleanStats);
    } else {
        echo "[]";
    }
?>;

const toutesCommandes = <?php
    if (!empty($commandes)) {
        $cleanCmd = [];
        foreach ($commandes as $c) {
            $cleanCmd[] = [
                'menu_titre' => $c['menu_titre'] ?? 'Inconnu',
                'prix_total' => $c['prix_total'] ?? 0,
                'statut'     => $c['statut_id'] ?? 0,
                'created_at' => $c['created_at'] ?? ''
            ];
        }
        echo json_encode($cleanCmd);
    } else {
        echo "[]";
    }
?>;
// --- FIN DU BLOC SECURISE ---


// Couleurs
const couleurs = [
    'rgba(196,151,58,0.85)',
    'rgba(45,74,45,0.85)',
    'rgba(74,122,74,0.85)',
    'rgba(127,119,221,0.85)',
];
 
let typeGraphique = 'bar';
let chartInstance = null;
 
function creerGraphique(type) {
    const ctx = document.getElementById('graphiqueMenus').getContext('2d');
    if (chartInstance) chartInstance.destroy();
 
    chartInstance = new Chart(ctx, {
        type: type,
        data: {
            labels: donneesMenus.map(d => d.titre),
            datasets: [{
                label: 'Nombre de commandes',
                data: donneesMenus.map(d => d.nb_commandes),
                backgroundColor: couleurs,
                borderColor: couleurs.map(c => c.replace('0.85', '1')),
                borderWidth: 1,
                borderRadius: type === 'bar' ? 6 : 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: type === 'pie' },
                tooltip: {
                    callbacks: {
                        afterLabel: (ctx) => {
                            const ca = donneesMenus[ctx.dataIndex].ca;
                            return 'CA : ' + ca.toLocaleString('fr-FR', {style:'currency', currency:'EUR'});
                        }
                    }
                }
            },
            scales: type === 'bar' ? {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            } : {}
        }
    });
}
 
function changerGraphique(type, btn) {
    typeGraphique = type === 'barres' ? 'bar' : 'pie';
    document.querySelectorAll('.admin-chart-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    creerGraphique(typeGraphique);
}
 
// Filtre CA
function mettreAJourCA() {
    const menuFiltre  = document.getElementById('filtre-menu-ca').value;
    const dateDebut   = document.getElementById('filtre-date-debut').value;
    const dateFin     = document.getElementById('filtre-date-fin').value;
 
    let cmdFiltrees = toutesCommandes.filter(c => c.statut == 7);
 
    if (menuFiltre) {
        cmdFiltrees = cmdFiltrees.filter(c => c.menu_titre === menuFiltre);
    }
    if (dateDebut) {
        cmdFiltrees = cmdFiltrees.filter(c => c.created_at >= dateDebut);
    }
    if (dateFin) {
        cmdFiltrees = cmdFiltrees.filter(c => c.created_at <= dateFin + ' 23:59:59');
    }
 
    const ca = cmdFiltrees.reduce((sum, c) => sum + parseFloat(c.prix_total), 0);
    document.getElementById('ca-filtre-val').textContent =
        ca.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
    document.getElementById('ca-filtre-nb').textContent = cmdFiltrees.length + ' commande(s) terminée(s)';
}
 
function reinitialiserFiltres() {
    document.getElementById('filtre-menu-ca').value = '';
    document.getElementById('filtre-date-debut').value = '';
    document.getElementById('filtre-date-fin').value = '';
    mettreAJourCA();
}
 
// Onglets principaux
document.querySelectorAll('.compte-tab').forEach(button => {
    button.onclick = function() {
        const targetName = this.getAttribute('data-tab');
        const targetPanel = document.getElementById('tab-' + targetName);

        if(targetPanel){
            document.querySelectorAll('.compte-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.compte-panel').forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            targetPanel.classList.add('active');
        } else {
            console.error("Erreur : Impossible de trouver le panneau ID'tab-" + targetName + "'");
        }
    };
});
 
// Sous-onglets
document.querySelectorAll('.employe-sous-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        const parent = this.closest('.compte-panel');
        parent.querySelectorAll('.employe-sous-tab').forEach(t => t.classList.remove('active'));
        parent.querySelectorAll('.employe-sous-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.sous).classList.add('active');
    });
});
 
function toggleForm(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
 
function ouvrirAnnulationAdmin(id) { document.getElementById('annulation-' + id).style.display = 'block'; }
function fermerAnnulationAdmin(id) { document.getElementById('annulation-' + id).style.display = 'none'; }
 
function filtrerCommandes() {
    const statut = document.getElementById('filtre-statut').value;
    const client = document.getElementById('filtre-client').value.toLowerCase();
    const items  = document.querySelectorAll('.employe-commande-item');
    let visible  = 0;

    items.forEach(item => {
        const matchStatut = !statut || item.dataset.statut == statut;
        const matchClient = !client || item.dataset.client.includes(client);

        const ok = matchStatut && matchClient;
        item.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
}

function filterPlats() {
    let nameSearch = document.getElementById('filterPlatName').value.toLowerCase();
    let menuSearch = document.getElementById('filterPlatMenu').value;

    let cards = document.querySelectorAll('#admin-sous-plats .employe-catalogue-item');

    let emptyMessage = document.getElementById('no-results-plats');

    let visibleCount = 0;

    cards.forEach(card => {
        let title = card.querySelector('.commande-item__num').innerText.toLowerCase();
        let menuName = card.querySelector('small strong').innerText;

        let nameMatch = title.includes(nameSearch);
        let menuMatch = (menuSearch === "" || menuName === menuSearch);

        if(nameMatch && menuMatch) {
            card.style.display = "block";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    if(emptyMessage) {
        emptyMessage.style.display = (visibleCount === 0) ? "block" : "none";
    }
}
 
function toggleHoraire(checkbox, jour) {
    const heures = document.getElementById('heures-' + jour);
    const label  = checkbox.closest('.horaire-toggle').querySelector('.horaire-toggle__label');
    heures.style.opacity       = checkbox.checked ? '1' : '0.3';
    heures.style.pointerEvents = checkbox.checked ? 'auto' : 'none';
    label.textContent          = checkbox.checked ? 'Ouvert' : 'Fermé';
}

// Initialiser le graphique si on arrive directement sur l'onglet stats
if (document.getElementById('tab-statistiques').classList.contains('active')) {
    creerGraphique(typeGraphique);
}

function editMenu(data){
    document.getElementById('modalMenuTitle').innerText = "Modifier le menu : " + data.titre;

    document.getElementById('menu_id').value = data.menu_id;
    document.getElementById('menu_titre').value = data.titre;
    document.getElementById('menu_prix').value = data.prix_par_personne;
    document.getElementById('menu_nb_pers').value = data.nombre_personne_minimum;
    document.getElementById('menu_description').value = data.description;
    document.getElementById('menu_conditions').value = data.conditions;

    document.getElementById('menu_theme').value = data.theme_id;
    document.getElementById('menu_regime').value = data.regime_id;
    document.getElementById('menu_actif').value = data.actif ? '1' : '0';
}

function resetMenuModal(){
    document.getElementById('modalMenuTitle').innerText = "Créer un nouveau menu";
    document.getElementById('menu_id'). value = "";
    document.getElementById('menu_form').reset();
}

</script>
 
<?php require_once '../includes/footer.php'; ?>