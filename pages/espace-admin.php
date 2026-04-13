<?php 
session_start();

$pageTitle = 'Espace Administrateur';
$rootPath = '../';
$curentPage = 'espace-admin';

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';


$estConnecte = isset($_SESSION['user_id']);

// if(!estConnecte) {
//     header('Location: ' . $rootPath . 'connexion.php');
//     exit();
// }

//Données fictives admin
$admin = [
    'admin_id' => 1,
    'prenom' => 'José',
    'nom' => 'Ferreira',
    'email' => 'jose.ferreira@juliescatering.fr',
    'role' => 'Administrateur',
];

//Données fictives employés
$employes = [
    [
        'employe_id' => 1,
        'prenom' => 'Lucas',
        'nom' => 'Martin',
        'email' => 'lucas.martin@juliescatering.fr',
        'role' => 'Chef de cuisine',
        'actif' => true,
        'created_at' => '2025-01-15 09:00:00',
        'last_login' => '2026-04-08 14:32:00',
    ],
    [
        'employe_id' => 2,
        'prenom' => 'Camille',
        'nom' => 'Rousseau',
        'email' => 'camille.rousseau@juliescatering.fr',
        'actif' => true,
        'created_at' => '2025-03_10 10:00:00',
        'last_login' => '2026-04-07 09:15:00',
    ],
    [
        'employe_id' => 3,
        'prenom' => 'Thomas',
        'nom' => 'Petit',
        'email' => 'thomas.petit@juliescatering.fr',
        'role' => 'Cuisinier',
        'actif' => false,
        'created_at' => '2025-06-01 08:30:00',
        'last_login' => '2025-12-20 11:00:00',
    ],
];

//Données fictives commandes (même structure que employé)
$commandes = [
    [
        'commande_id' => 1001,
        'client_prenom' => 'Marie',
        'client_nom' => 'Dupont',
        'client_email' => 'marie.dupont@email.com',
        'client_telephone' => '06 12 34 56 78',
        'menu_titre' => 'Le Grand Festin de Noël',
        'nb_personnes' => 12,
        'date_prestation' => '2026-12-24',
        'heure_prestation' => '19:00',
        'adresse_prestation' => '5 allée des Pins, 33000 Bordeaux',
        'prix_total' => 384.00,
        'statut' => 'en_attente',
        'created_at' => '2026-11-10 14:32:00',
        'materiel_prete' => true,
        'historique' => [
            ['statut' => 'en_attente', 'date' => '2026-11-10 14:32:00', 'auteur' => 'Système'],
        ],
    ],
[
    'commande_id' => 1002, 
    'client_prenom' => 'Jean',
    'client_nom' => 'Leclerc',
    'client_email' => 'jean.leclerc@email.com',
    'client_telephone' => '06 98 76 54 32',
    'menu_titre' => 'Menu Prestige Classique',
    'nb_personnes' => 20, 
    'date_prestation' => '2026-09-15',
    'heure_prestation' => '12:30',
    'adresse_prestation' => '18 rue du Château, 33100 Bordeaux',
    'prix_total' => 450.00,
    'statut' => 'terminee',
    'created_at' => '2026-08-01 10:00:00',
    'materiel_prete' => false,
    'historique' => [
        ['statut' => 'en_attente', 'date' => '2026-08-01 10:00:00', 'auteur' => 'Système'],
        ['statut' => 'accepte', 'date' => '2026-08-02 09:15:00', 'auteur' => 'Lucas Martin'],
        ['statut' => 'terminee', 'date' => '2026-09-15 13:00:00', 'auteur' => 'Lucas Martin'],
    ],
],
[
        'commande_id'        => 1003,
        'client_prenom'      => 'Sophie',
        'client_nom'         => 'Bernard',
        'client_email'       => 'sophie.b@email.com',
        'client_telephone'   => '07 11 22 33 44',
        'menu_titre'         => 'Printemps & Pâques',
        'nb_personnes'       => 8,
        'date_prestation'    => '2026-04-20',
        'heure_prestation'   => '13:00',
        'adresse_prestation' => '3 impasse des Lilas, 33200 Bordeaux',
        'prix_total'         => 180.00,
        'statut'             => 'terminee',
        'created_at'         => '2026-03-15 16:45:00',
        'materiel_prete'     => false,
        'historique'         => [
            ['statut' => 'en_attente',  'date' => '2026-03-15 16:45:00', 'auteur' => 'Système'],
            ['statut' => 'accepte',     'date' => '2026-03-16 10:00:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'terminee',    'date' => '2026-04-20 13:10:00', 'auteur' => 'Lucas Martin'],
        ],
    ],
    [
        'commande_id'        => 1004,
        'client_prenom'      => 'Pierre',
        'client_nom'         => 'Moreau',
        'client_email'       => 'pierre.moreau@email.com',
        'client_telephone'   => '06 55 44 33 22',
        'menu_titre'         => 'Le Grand Festin de Noël',
        'nb_personnes'       => 30,
        'date_prestation'    => '2026-05-10',
        'heure_prestation'   => '19:30',
        'adresse_prestation' => '7 avenue Victor Hugo, 33000 Bordeaux',
        'prix_total'         => 960.00,
        'statut'             => 'terminee',
        'created_at'         => '2026-04-01 09:00:00',
        'materiel_prete'     => true,
        'historique'         => [
            ['statut' => 'en_attente', 'date' => '2026-04-01 09:00:00', 'auteur' => 'Système'],
            ['statut' => 'accepte',    'date' => '2026-04-02 10:00:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'terminee',   'date' => '2026-05-10 20:00:00', 'auteur' => 'Lucas Martin'],
        ],
    ],
    [
        'commande_id'        => 1005,
        'client_prenom'      => 'Claire',
        'client_nom'         => 'Dubois',
        'client_email'       => 'claire.dubois@email.com',
        'client_telephone'   => '06 33 22 11 00',
        'menu_titre'         => 'Menu Prestige Classique',
        'nb_personnes'       => 15,
        'date_prestation'    => '2026-03-01',
        'heure_prestation'   => '12:00',
        'adresse_prestation' => '2 rue de la Liberté, 33000 Bordeaux',
        'prix_total'         => 337.50,
        'statut'             => 'terminee',
        'created_at'         => '2026-02-01 10:00:00',
        'materiel_prete'     => false,
        'historique'         => [
            ['statut' => 'en_attente', 'date' => '2026-02-01 10:00:00', 'auteur' => 'Système'],
            ['statut' => 'terminee',   'date' => '2026-03-01 13:00:00', 'auteur' => 'Camille Rousseau'],
        ],
    ],
];

//Avis (même que employé)
$avis = [
    [
        'avis_id'     => 1,
        'commande_id' => 1003,
        'client'      => 'Sophie Bernard',
        'menu_titre'  => 'Printemps & Pâques',
        'note'        => 5,
        'commentaire' => 'Excellent service, tout était parfait !',
        'date'        => '2026-04-21 10:30:00',
        'statut'      => 'en_attente',
    ],
    [
        'avis_id'     => 2,
        'commande_id' => 1004,
        'client'      => 'Pierre Moreau',
        'menu_titre'  => 'Le Grand Festin de Noël',
        'note'        => 4,
        'commentaire' => 'Très bonne prestation, équipe professionnelle.',
        'date'        => '2026-05-11 09:00:00',
        'statut'      => 'en_attente',
    ],
];

//Menus et plats
$menus = [
    ['menu_id' => 1, 'titre' => 'Le Grand Festin de Noël',   'prix_par_personne' => 32.00, 'nb_plats' => 6, 'actif' => true],
    ['menu_id' => 2, 'titre' => 'Menu Prestige Classique',   'prix_par_personne' => 22.50, 'nb_plats' => 4, 'actif' => true],
    ['menu_id' => 3, 'titre' => 'Printemps & Pâques',        'prix_par_personne' => 22.50, 'nb_plats' => 5, 'actif' => true],
    ['menu_id' => 4, 'titre' => 'Soirée Entreprise Premium', 'prix_par_personne' => 25.00, 'nb_plats' => 5, 'actif' => false],
];
 
$plats = [
    ['plat_id' => 1, 'nom' => 'Foie gras maison',        'categorie' => 'Entrée',  'allergenes' => 'Gluten',              'actif' => true],
    ['plat_id' => 2, 'nom' => 'Velouté de champignons',  'categorie' => 'Entrée',  'allergenes' => 'Lait',                'actif' => true],
    ['plat_id' => 3, 'nom' => 'Magret de canard',        'categorie' => 'Plat',    'allergenes' => '—',                   'actif' => true],
    ['plat_id' => 4, 'nom' => 'Risotto aux truffes',     'categorie' => 'Plat',    'allergenes' => 'Lait',                'actif' => true],
    ['plat_id' => 5, 'nom' => 'Bûche de Noël chocolat',  'categorie' => 'Dessert', 'allergenes' => 'Gluten, Lait, Œufs',  'actif' => true],
    ['plat_id' => 6, 'nom' => 'Sorbet fruits de saison', 'categorie' => 'Dessert', 'allergenes' => '—',                   'actif' => false],
];

//Horaires
$horaires = [
    ['jour' => 'Lundi',    'ouvert' => false, 'debut' => '',      'fin' => ''],
    ['jour' => 'Mardi',    'ouvert' => true,  'debut' => '09:00', 'fin' => '18:00'],
    ['jour' => 'Mercredi', 'ouvert' => true,  'debut' => '09:00', 'fin' => '18:00'],
    ['jour' => 'Jeudi',    'ouvert' => true,  'debut' => '09:00', 'fin' => '20:00'],
    ['jour' => 'Vendredi', 'ouvert' => true,  'debut' => '09:00', 'fin' => '20:00'],
    ['jour' => 'Samedi',   'ouvert' => true,  'debut' => '10:00', 'fin' => '22:00'],
    ['jour' => 'Dimanche', 'ouvert' => false, 'debut' => '',      'fin' => ''],
];

//Labels et couleurs statuts
$statutLabels = [
    'en_attente'            => 'En attente',
    'accepte'               => 'Acceptée',
    'en_preparation'        => 'En préparation',
    'en_cours_de_livraison' => 'En cours de livraison',
    'livre'                 => 'Livrée',
    'en_attente_materiel'   => 'Retour matériel',
    'terminee'              => 'Terminée',
    'annulee'               => 'Annulée',
];
 
$statutColors = [
    'en_attente'            => 'statut--attente',
    'accepte'               => 'statut--accepte',
    'en_preparation'        => 'statut--prep',
    'en_cours_de_livraison' => 'statut--livraison',
    'livre'                 => 'statut--livre',
    'en_attente_materiel'   => 'statut--materiel',
    'terminee'              => 'statut--termine',
    'annulee'               => 'statut--annule',
];
 
$statutTransitions = [
    'en_attente'            => ['accepte', 'annulee'],
    'accepte'               => ['en_preparation'],
    'en_preparation'        => ['en_cours_de_livraison'],
    'en_cours_de_livraison' => ['livre'],
    'livre'                 => ['en_attente_materiel', 'terminee'],
    'en_attente_materiel'   => ['terminee'],
];


//Stats
$nbEnAttente = count(array_filter($commandes, fn($c) => $c['statut'] === 'en_attente'));
$nbEnCours = count(array_filter($commandes, fn($c) => in_array($c['statut'], ['accepte', 'en_preparation', 'en_cours_de_livraison', 'livre', 'en_attente_materiel'])));
$nbAvisAttente = count(array_filter($avis, fn($a) => $a['statut'] === 'en_attente'));
$nbEmployes = count(array_filter($employes, fn($e) => $e['actif']));
$caTotal = array_sum(array_column(array_filter($commandes, fn($c) => $c['statut'] === 'terminee'), 'prix_total'));

//Stats par menu (pour graphique)
$statsParMenu = [];
foreach($commandes as $cmd) {
    $titre = $cmd['menu_titre'];
    if(!isset($statsParMenu[$titre])) {
        $statsParMenu[$titre] = ['nb_commandes' => 0, 'ca' => 0.0];
    }
    $statsParMenu[$titre]['nb_commandes']++;
    if($cmd['statut'] === 'terminee') {
    $statsParMenu[$titre]['ca'] += $cmd['prix_total'];
    }
}

//Traitement POST
$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'creer_employe':
            $succes = 'Compte employé créé pour ' . htmlspecialchars($_POST['email'] ?? '') . '. Un e-mail de notification lui a été envoyé.';
            break;
        case 'toggle_employe':
            $succes = 'Statut du compte employé mis à jour.';
            break;
        case 'update_statut':
            $succes = 'Statut de la commande #' . (int)($_POST['commande_id'] ?? 0) . ' mis à jour.';
            break;
        case 'annuler_commande':
            $succes = 'Commande #' . (int)($_POST['commande_id'] ?? 0) . ' annulée.';
            break;
        case 'valider_avis':
            $succes = 'Avis publié avec succès.';
            break;
        case 'refuser_avis':
            $succes = 'Avis refusé.';
            break;
        case 'update_menu':
            $succes = 'Menu mis à jour.';
            break;
        case 'delete_menu':
            $succes = 'Menu supprimé.';
            break;
        case 'update_plat':
            $succes = 'Plat mis à jour.';
            break;
        case 'delete_plat':
            $succes = 'Plat supprimé.';
            break;
        case 'update_horaires':
            $succes = 'Horaires mis à jour.';
            break;
    }
}
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
                <div>
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
                        Créé le <?= date('d/m/Y', strtotime($emp['created_at'])) ?>
                        · Dernière connexion : <?= date('d/m/Y à H:i', strtotime($emp['last_login'])) ?>
                    </div>
                </div>
            </div>
            <div class="admin-employe-card__actions">
                <form method="POST" action="" style="display:inline;"
                    onsubmit="return confirm('<?= $emp['actif'] ? 'Désactiver' : 'Réactiver' ?> ce compte employé ?')">
                    <input type="hidden" name="action" value="toggle_employe">
                    <input type="hidden" name="employe_id" value="<?= $emp['employe_id'] ?>">
                    <input type="hidden" name="actif" value="<?= $emp['actif'] ? '0' : '1' ?>">
                    <button type="submit" class="btn-compte-action <?= $emp['actif'] ? 'btn-compte-action--annuler' : 'btn-compte-action--modifier' ?>">
                        <?= $emp['actif'] ? '✕ Désactiver' : '✓ Réactiver' ?>
                    </button>
                </form>
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
                            <?php foreach ($menus as $m): ?>
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
                        <div class="admin-ca-card__sub" id="ca-filtre-nb"><?= count(array_filter($commandes, fn($c) => $c['statut'] === 'terminee')) ?> commandes terminées</div>
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
                                            <div class="admin-progress-bar">
                                                <div class="admin-progress-bar__fill" style="width:<?= $part ?>%"></div>
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
                             data-statut="<?= $cmd['statut'] ?>"
                             data-client="<?= strtolower($cmd['client_prenom'] . ' ' . $cmd['client_nom'] . ' ' . $cmd['client_email']) ?>"
                             id="admin-cmd-<?= $cmd['commande_id'] ?>">
 
                            <div class="commande-item__header">
                                <div class="commande-item__id">
                                    <span class="commande-item__num">Commande #<?= $cmd['commande_id'] ?></span>
                                    <span class="commande-statut <?= $statutColors[$cmd['statut']] ?? '' ?>">
                                        <?= $statutLabels[$cmd['statut']] ?? $cmd['statut'] ?>
                                    </span>
                                    <?php if ($cmd['materiel_prete']): ?>
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
                                                <a href="mailto:<?= $cmd['client_email'] ?>"><?= $cmd['client_email'] ?></a>
                                                &nbsp;·&nbsp;
                                                <a href="tel:<?= $cmd['client_telephone'] ?>"><?= $cmd['client_telephone'] ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="commande-info-row">
                                        <span class="commande-info-label">Menu</span>
                                        <span class="commande-info-val"><?= htmlspecialchars($cmd['menu_titre']) ?></span>
                                    </div>
                                    <div class="commande-info-row">
                                        <span class="commande-info-label">Personnes</span>
                                        <span class="commande-info-val"><?= $cmd['nb_personnes'] ?> pers.</span>
                                    </div>
                                    <div class="commande-info-row">
                                        <span class="commande-info-label">Date</span>
                                        <span class="commande-info-val"><?= date('d/m/Y', strtotime($cmd['date_prestation'])) ?> à <?= $cmd['heure_prestation'] ?></span>
                                    </div>
                                </div>
 
                                <div class="commande-suivi">
                                    <h4 class="commande-suivi__titre">Historique</h4>
                                    <ul class="suivi-timeline">
                                        <?php foreach ($cmd['historique'] as $etape): ?>
                                            <li class="suivi-etape suivi-etape--done">
                                                <div class="suivi-etape__dot"></div>
                                                <div class="suivi-etape__content">
                                                    <span class="suivi-etape__label"><?= $statutLabels[$etape['statut']] ?? $etape['statut'] ?></span>
                                                    <span class="suivi-etape__date"><?= date('d/m/Y H:i', strtotime($etape['date'])) ?> — <?= $etape['auteur'] ?></span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
 
                            <div class="commande-item__actions employe-commande-actions">
                                <?php if (isset($statutTransitions[$cmd['statut']])): ?>
                                    <form method="POST" action="" style="display:inline-flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="action" value="update_statut">
                                        <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                        <select name="nouveau_statut" class="commande-input employe-statut-select">
                                            <?php foreach ($statutTransitions[$cmd['statut']] as $s): ?>
                                                <option value="<?= $s ?>"><?= $statutLabels[$s] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-compte-action btn-compte-action--modifier">
                                            Mettre à jour →
                                        </button>
                                    </form>
                                <?php endif; ?>
 
                                <?php if (!in_array($cmd['statut'], ['terminee', 'annulee'])): ?>
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
 
            <!-- ====== ONGLET AVIS ====== -->
            <div class="compte-panel" id="tab-avis-employe">
                <?php
                $avisEnAttente = array_filter($avis, fn($a) => $a['statut'] === 'en_attente');
                $avisTraites   = array_filter($avis, fn($a) => $a['statut'] !== 'en_attente');
                ?>
                <?php if (!empty($avisEnAttente)): ?>
                    <h3 class="employe-section-titre">À valider (<?= count($avisEnAttente) ?>)</h3>
                    <div class="commandes-liste">
                        <?php foreach ($avisEnAttente as $a): ?>
                            <div class="commande-item">
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
                    </div>
                <?php else: ?>
                    <div class="compte-empty">
                        <h3>Aucun avis en attente</h3>
                        <p>Tous les avis ont été traités.</p>
                    </div>
                <?php endif; ?>
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
                        <form method="POST" action="" class="modif-form">
                            <input type="hidden" name="action" value="update_menu">
                            <h4 class="modif-form__titre">Nouveau menu</h4>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label class="commande-label">Titre</label>
                                    <input type="text" name="titre" class="commande-input" required>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Prix / pers. (€)</label>
                                    <input type="number" name="prix" class="commande-input" step="0.50" min="0" required>
                                </div>
                            </div>
                            <div class="modif-form__actions">
                                <button type="submit" class="btn btn-vg-primary">Créer</button>
                                <button type="button" class="btn btn-vg-secondary"
                                        onclick="toggleForm('form-admin-nouveau-menu')">Annuler</button>
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
                    <div class="commande-modif-form" id="form-admin-nouveau-plat" style="display:none;">
                        <form method="POST" action="" class="modif-form">
                            <input type="hidden" name="action" value="update_plat">
                            <h4 class="modif-form__titre">Nouveau plat</h4>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label class="commande-label">Nom</label>
                                    <input type="text" name="nom" class="commande-input" required>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Catégorie</label>
                                    <select name="categorie" class="commande-input">
                                        <option>Entrée</option><option>Plat</option>
                                        <option>Dessert</option><option>Boisson</option>
                                    </select>
                                </div>
                            </div>
                            <div class="commande-field">
                                <label class="commande-label">Allergènes</label>
                                <input type="text" name="allergenes" class="commande-input" placeholder="Ex : Gluten, Lait">
                            </div>
                            <div class="modif-form__actions">
                                <button type="submit" class="btn btn-vg-primary">Créer</button>
                                <button type="button" class="btn btn-vg-secondary"
                                        onclick="toggleForm('form-admin-nouveau-plat')">Annuler</button>
                            </div>
                        </form>
                    </div>
                    <div class="employe-catalogue">
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
                                            onclick="toggleForm('form-admin-plat-<?= $plat['plat_id'] ?>')">Modifier</button>
                                    <form method="POST" action="" style="display:inline;"
                                          onsubmit="return confirm('Supprimer ce plat ?')">
                                        <input type="hidden" name="action" value="delete_plat">
                                        <input type="hidden" name="plat_id" value="<?= $plat['plat_id'] ?>">
                                        <button type="submit" class="btn-compte-action btn-compte-action--annuler">Supprimer</button>
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
                                               class="commande-input horaire-input" value="<?= $h['debut'] ?>">
                                        <span class="horaire-sep">→</span>
                                        <input type="time" name="fin[<?= $h['jour'] ?>]"
                                               class="commande-input horaire-input" value="<?= $h['fin'] ?>">
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
// Données pour le graphique (injectées depuis PHP)
const donneesMenus = <?= json_encode(array_map(fn($titre, $stats) => [
    'titre'        => $titre,
    'nb_commandes' => $stats['nb_commandes'],
    'ca'           => $stats['ca'],
], array_keys($statsParMenu), array_values($statsParMenu))) ?>;
 
const toutesCommandes = <?= json_encode(array_map(fn($c) => [
    'menu_titre'  => $c['menu_titre'],
    'prix_total'  => $c['prix_total'],
    'statut'      => $c['statut'],
    'created_at'  => $c['created_at'],
], $commandes)) ?>;
 
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
 
    let cmdFiltrees = toutesCommandes.filter(c => c.statut === 'terminee');
 
    if (menuFiltre) {
        cmdFiltrees = cmdFiltrees.filter(c => c.menu_titre === menuFiltre);
    }
    if (dateDebut) {
        cmdFiltrees = cmdFiltrees.filter(c => c.created_at >= dateDebut);
    }
    if (dateFin) {
        cmdFiltrees = cmdFiltrees.filter(c => c.created_at <= dateFin + ' 23:59:59');
    }
 
    const ca = cmdFiltrees.reduce((sum, c) => sum + c.prix_total, 0);
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
document.querySelectorAll('.compte-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.compte-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.compte-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        const panel = document.getElementById('tab-' + this.dataset.tab);
        if (panel) panel.classList.add('active');
        if (this.dataset.tab === 'statistiques') creerGraphique(typeGraphique);
    });
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
        const ok = (!statut || item.dataset.statut === statut) &&
                   (!client || item.dataset.client.includes(client));
        item.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
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

</script>
 
<?php require_once '../includes/footer.php'; ?>