<?php
session_start();

$pageTitle = 'Espace Employé';
$rootPath = '../';
$currentPage = 'espace-employe';

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';


$estConnecte = isset($_SESSION['employe_id']);

// if (!estConnecte) {
//     header('Location: ' . $rootPath . 'pages/connexion.php');
//     exit();
// }

//Données fictives employé
$employe  = [
    'employe_id' => 1,
    'prenom' => 'Jean',
    'nom' => 'Dupont',
    'email' => 'lucasmartin@gmail.com',
    'role' => 'Chef de cuisine',
];

//Données fictives commandes
$commandes = [
    [
        'commande_id' => 1,
        'client_prenom' => 'Marie',
        'client_nom' => 'Durand',
        'client_email' => 'mariedurand@gmail.com',
        'client_telephone' => '0601020304',
        'menu_titre' => 'Le Grand Festin de Noël',
        'nb_personnes' => 12,
        'date_prestation' => '2026-12-24',
        'heure_prestation' => '19:00',
        'adresse_prestation' => '123 Rue de la Paix, Paris',
        'prix_total' => 384.00,
        'statut' => 'en_attente',
        'created_at' => '2026-11-10 14:30:00',
        'materiel_prete' => 'true',
        'historique' => [
            ['statut' => 'en_attente', 'date' => '2026-11-10 14:30:00', 'auteur' => 'système'],
        ],
    ],
    [
        'commande_id'        => 1002,
        'client_prenom'      => 'Jean',
        'client_nom'         => 'Leclerc',
        'client_email'       => 'jean.leclerc@email.com',
        'client_telephone'   => '06 98 76 54 32',
        'menu_titre'         => 'Menu Prestige Classique',
        'nb_personnes'       => 20,
        'date_prestation'    => '2026-09-15',
        'heure_prestation'   => '12:30',
        'adresse_prestation' => '18 rue du Château, 33100 Bordeaux',
        'prix_total'         => 450.00,
        'statut'             => 'accepte',
        'created_at'         => '2026-08-01 10:00:00',
        'materiel_prete'     => false,
        'historique'         => [
            ['statut' => 'en_attente', 'date' => '2026-08-01 10:00:00', 'auteur' => 'Système'],
            ['statut' => 'accepte',    'date' => '2026-08-02 09:15:00', 'auteur' => 'Lucas Martin'],
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
        'statut'             => 'en_preparation',
        'created_at'         => '2026-03-15 16:45:00',
        'materiel_prete'     => false,
        'historique'         => [
            ['statut' => 'en_attente',    'date' => '2026-03-15 16:45:00', 'auteur' => 'Système'],
            ['statut' => 'accepte',       'date' => '2026-03-16 10:00:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'en_preparation','date' => '2026-04-19 14:00:00', 'auteur' => 'Lucas Martin'],
        ],
    ],
    [
        'commande_id'        => 1004,
        'client_prenom'      => 'Pierre',
        'client_nom'         => 'Moreau',
        'client_email'       => 'pierre.moreau@email.com',
        'client_telephone'   => '06 55 44 33 22',
        'menu_titre'         => 'Soirée Entreprise Premium',
        'nb_personnes'       => 50,
        'date_prestation'    => '2026-05-10',
        'heure_prestation'   => '19:30',
        'adresse_prestation' => '7 avenue Victor Hugo, 33000 Bordeaux',
        'prix_total'         => 1250.00,
        'statut'             => 'terminee',
        'created_at'         => '2026-04-01 09:00:00',
        'materiel_prete'     => true,
        'historique'         => [
            ['statut' => 'en_attente',              'date' => '2026-04-01 09:00:00', 'auteur' => 'Système'],
            ['statut' => 'accepte',                 'date' => '2026-04-02 10:00:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'en_preparation',          'date' => '2026-05-09 14:00:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'en_cours_de_livraison',   'date' => '2026-05-10 18:00:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'livre',                   'date' => '2026-05-10 19:20:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'en_attente_materiel',     'date' => '2026-05-10 19:25:00', 'auteur' => 'Lucas Martin'],
            ['statut' => 'terminee',                'date' => '2026-05-20 10:00:00', 'auteur' => 'Lucas Martin'],
        ],
    ],
];

//Données fictives avis à valider
$avis = [
    [
        'avis_id' => 1,
        'commande_id' => 1002,
        'client' => 'Sophie Bernard',
        'menu_titre' => 'Printemps & Pâques',
        'note' => 4,
        'commentaire' => 'Très bon repas, service impeccable.',
        'date' => '2026-04-25 10:00:00',
        'statut' => 'en_attente',
    ],
    [
        'avis_id' => 2,
        'commande_id' => 1004,
        'client' => 'Pierre Moreau',
        'menu_titre' => 'Soirée Entreprise Premium',
        'note' => 5,
        'commentaire' => 'Excellente prestation, tout le monde a adoré !',
        'date' => '2026-05-15 14:30:00',
        'statut' => 'en_attente',
    ],
    [
        'avis_id' => 3,
        'commande_id' => 1003,
        'client' => 'Jean Leclerc',
        'menu_titre' => 'Menu Prestige Classique',
        'note' => 3,
        'commentaire' => 'Bon repas mais un peu en retard.',
        'date' => '2026-04-20 12:00:00',
        'statut' => 'en_attente',
    ],
];

//Données fictives plats 
$plats = [
    ['plat_id' => 1, 'nom' => 'Foie gras maison',           'categorie' => 'Entrée',  'allergenes' => 'Gluten', 'actif' => true],
    ['plat_id' => 2, 'nom' => 'Velouté de champignons',     'categorie' => 'Entrée',  'allergenes' => 'Lait',   'actif' => true],
    ['plat_id' => 3, 'nom' => 'Magret de canard',           'categorie' => 'Plat',    'allergenes' => '—',      'actif' => true],
    ['plat_id' => 4, 'nom' => 'Risotto aux truffes',        'categorie' => 'Plat',    'allergenes' => 'Lait',   'actif' => true],
    ['plat_id' => 5, 'nom' => 'Bûche de Noël chocolat',     'categorie' => 'Dessert', 'allergenes' => 'Gluten, Lait, Œufs', 'actif' => true],
    ['plat_id' => 6, 'nom' => 'Sorbet fruits de saison',    'categorie' => 'Dessert', 'allergenes' => '—',      'actif' => false],
];

//Données fictives menus 
$menus = [
    ['menu_id' => 1, 'titre' => 'Le Grand Festin de Noël', 'prix_par_personne' => 32.00, 'nb_plats' => 6, 'actif' => true],
    ['menu_id' => 2, 'titre' => 'Menu Prestige Classique', 'prix_par_personne' => 22.50, 'nb_plats' => 4, 'actif' => true],
    ['menu_id' => 3, 'titre' => 'Printemps & Pâques', 'prix_par_personne' => 22.50, 'nb_plats' => 5, 'actif' => false],
];

//Données fictives horaires
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
    'en_attente'              => 'En attente',
    'accepte'                 => 'Acceptée',
    'en_preparation'          => 'En préparation',
    'en_cours_de_livraison'   => 'En cours de livraison',
    'livre'                   => 'Livrée',
    'en_attente_materiel'     => 'Retour matériel',
    'terminee'                => 'Terminée',
    'annulee'                 => 'Annulée',
];
 
$statutColors = [
    'en_attente'              => 'statut--attente',
    'accepte'                 => 'statut--accepte',
    'en_preparation'          => 'statut--prep',
    'en_cours_de_livraison'   => 'statut--livraison',
    'livre'                   => 'statut--livre',
    'en_attente_materiel'     => 'statut--materiel',
    'terminee'                => 'statut--termine',
    'annulee'                 => 'statut--annule',
];
 
// Transitions autorisées par statut
$statutTransitions = [
    'en_attente'            => ['accepte', 'annulee'],
    'accepte'               => ['en_preparation'],
    'en_preparation'        => ['en_cours_de_livraison'],
    'en_cours_de_livraison' => ['livre'],
    'livre'                 => ['en_attente_materiel', 'terminee'],
    'en_attente_materiel'   => ['terminee'],
];

//Traitement POST
$erreur = "";
$succes = "";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'update_statut':
            $succes = 'Statut de la commande #' .(int)$_POST['commande_id'] . ' mis à jour avec succès.';
            break;
        case 'annuler_commande':
            $succes = 'Commande #' . (int)$_POST['commande_id'] . ' annulée avec succès.';
            break;
        case 'valider_avis':
            $succes = 'Avis #' . (int)$_POST['avis_id'] . ' validé avec succès.';
            break;
        case 'refuser_avis':
            $succes = 'Avis #' . (int)$_POST['avis_id'] . ' refusé avec succès.';
            break;
        case 'update_menu':
            $succes = 'Menu mis à jour avec succès.';
            break;
        case 'delete_menu':
            $succes = 'Menu supprimé avec succès.';
            break;
        case 'update_plat':
            $succes = 'Plat mis à jour avec succès.';
            break;
        case 'delete_plat':
            $succes = 'Plat supprimé avec succès.';
            break;
        case 'update_horaire':
            $succes = 'Horaires mis à jour avec succès.';
            break;
    }
}

//Stats rapides
$nbEnAttente = count(array_filter($commandes, fn($c) => $c['statut'] === 'en_attente'));
$nbEnCours = count(array_filter($commandes, fn($c) => in_array($c['statut'], ['en_preparation', 'en_cours_de_livraison', 'livre', 'en_attente_materiel'])));
$nbAvisAttente = count(array_filter($avis, fn($a) => $a['statut'] === 'en_attente'));
$caTotal = array_sum(array_column(array_filter($commandes, fn($c) => $c['statut'] === 'terminee'), 'prix_total'));

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
                    <?= htmlspecialchars($employe['role']) ?>
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

          <!-- Onglets Commandes -->
           <div class="compte-panel active" id="tab-commandes">

           <!-- Filtres -->
            <div class="employe-filtres">
                <div class="employe-filtre-group">
                    <label class="employe-filtre-label">Filtrer par statut</label>
                    <select id="filtre-statut" class="commande-input-employe-filtre-select" onchange="filtrerCommandes()">
                        <option value="">Tous les statuts</option>
                        <?php foreach($statutLabels as $val => $label): ?>
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

            <!-- Liste commandes -->
             <div class="commandes-liste" id="liste-commandes">
                <?php foreach($commandes as $cmd): ?>
                    <div class="commande-item employe-commande-item" data-statut="<?= $cmd['statut'] ?>" data-client="<?= strtolower($cmd['client_prenom'] . ' ' . $cmd['client_nom'] . ' ' . $cmd['client_email']) ?>" id="employe-cmd-<?= $cmd['commande_id'] ?>">
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
                                <!-- Info client -->
                                 <div class="employe-client-bloc">
                                    <div class="employe-client-avatar">
                                        <?= strtoupper(substr($cmd['client_prenom'], 0, 1) . substr($cmd['client_nom'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="employe-client-nom"><?= htmlspecialchars($cmd['client_prenom'] . ' ' . $cmd['client_nom']) ?></div>
                                        <div class="employe-client-contact">
                                            <a href="mailto:<?= $cmd['client_email'] ?>">
                                                <?= $cmd['client_email'] ?>
                                            </a>
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
                                 <div class="commande-info-row">
                                    <span class="commande-info-label">Adresse</span>
                                    <span class="commande-info-val"><?= htmlspecialchars($cmd['adresse_prestation']) ?></span>
                                 </div>
                            </div>

                            <!-- Timeline suivi -->
                             <div class="commande-suivi">
                                <h4 class="commande-de-suivi__titre">Historique</h4>
                                <ul class="suivi-timeline">
                                    <?php foreach($cmd['historique'] as $etape): ?>
                                        <li class="suivi-etape suivi-etape--done">
                                            <div class="suivi-etape__dot"></div>
                                            <div class="suivi-etape__content">
                                                <span class="suivi-etape__label"><?= $statutLabels[$etape['statut']] ?? $etape['statut'] ?></span>
                                                <span class="suivi-etape__date"><?= date('d/m/Y H:i', strtotime($etape['date'])) ?> - <?= $etape['auteur'] ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                             </div>
                        </div>

                        <!-- Actions employé -->
                         <div class="commande-item__actions employe-commande-actions">

                         <!-- Mise à jour du statut -->
                          <?php if(isset($statutTransitions[$cmd['statut']])): ?>
                            <form method="POST" action="" style="display:inline-flex; gap:8px; align-items:center;">
                                <input type="hidden" name="action" value="update_statut">
                                <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                <select name="nouveau_statut" class="commande-input employe-statut-select">
                                    <?php foreach($statutTransitions[$cmd['statut']] as $s): ?>
                                        <option value="<?= $s ?>"><?= $statutLabels[$s] ?></option>
                                        <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-compte-action btn-compte-action--modifier">
                                    Mettre à jour →
                                </button>
                                </form>
                                <?php endif; ?>
                                <!-- Annulation de commande -->
                                <?php if(!in_array($cmd['statut'], ['terminee', 'annulee'])): ?>
                                <button type="button" onclick="ouvrirAnnulationEmploye(<?= $cmd['commande_id'] ?>)" class="btn-compte-action btn-compte-action--annuler">
                                    Annuler commande
                                </button>
                                <?php endif; ?>
                         </div>

                         <!-- Formulaire annulation  avec modif -->
                          <div class="commande-modif-form employe-annulation-form" id="annulation-<?= $cmd['commande_id'] ?>" style="display:none;">
                            <form method="POST" action="" class="modif-form">
                                <input type="hidden" name="action" value="annuler_commande">
                                <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                <h4 class="modif-form__titre">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    Annuler la commande — contact client obligatoire
                                </h4>
                                <div class="employe-annulation-notice">
                                    Avant toute annulation, vous devez avoir contacté le client via appel ou e-mail.
                                </div>
                                <div class="commande-field-row">
                                    <div class="commande-field">
                                        <label class="commande-label">Mode de contact *</label>
                                        <select name="mode_contact" class="commande-input" required>
                                            <option value="">- Sélectionner -</option>
                                            <option value="appel_gsm">Appel Téléphonique</option>
                                            <option value="email">E-mail</option>
                                        </select>
                                    </div>
                                    <div class="commande-field">
                                        <label class="commande-label">Date du contact *</label>
                                        <input type="datetime-local" name="date-contact" class="commande-input" required>
                                    </div>
                                </div>
                                <div class="commande-field">
                                    <label class="commande-label">Motif d'annulation *</label>
                                    <textarea name="motif_annulation" class="commande-input avis-textarea" placeholder="Décrivez le motif d'annulation..." rows="3" required></textarea>
                                </div>
                                <div class="modif-form__actions">
                                    <button type="submit" class="btn btn-vg-primary">Confirmer l'annulation</button>
                                    <button type="button" class="btn btn-vg-secondary" onclick="fermerAnnulationEmploye(<?= $cmd['commande_id'] ?>)">
                                        Retour
                                    </button>
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
            $avisEnAttente = array_filter($avis, fn($a) => $a['statut'] === 'en_attente');
            $avisTraites = array_filter($avis, fn($a) => $a['statut'] !== 'en_attente');
        ?>

        <?php if (!empty($avisEnAttente)): ?>
            <h3 class="employe-section-titre">À valider (<?= count($avisEnAttente) ?>)</h3>
            <div class="commandes-liste">
                <?php foreach($avisEnAttente as $a): ?>
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
                                <button type="submit" class="btn-compte-action btn-compte-action--modifier"> ✓ Publier</button>
                            </form>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="refuser_avis">
                                <input type="hidden" name="avis_id" value="<?= $a['avis_id'] ?>">
                                <button type="submit" class="btn-compte-action btn-compte-action--annuler">
                                    ✕ Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="compte-empty">
                <h3>Aucun avis en attente</h3>
                <p>Tous les avis clients ont été traités.</p>
            </div>
            <?php endif; ?>

            <?php if (!empty($avisTraites)): ?>
                <h3 class="empoye-section-titre">Déjà traités</h3>
                <div class="commandes-liste">
                    <?php foreach($avisTraites as $a): ?>
                        <div class="commande-item commande-item--archive">
                            <div class="commande-item__header">
                                <div class="commande-item__id">
                                    <span class="commande-item__num"><?= htmlspecialchars($a['client']) ?></span>
                                    <?php if ($a['statut'] === 'valide'): ?>
                                        <span class="commande-statut statut--termine">Publié</span>
                                    <?php else: ?>
                                        <span class="commande-statut statut--annule">Refusé</span>
                                    <?php endif; ?>
                                </div>
                                <span class="commande-info-label"><?= date('d/m/Y', strtotime($a['date'])) ?></span>
                            </div>
                            <div class="avis-employe-body">
                                <div class="avis-donne__stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="<?= $i <= $a['note'] ? 'star--on' : 'star--off' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <p class="avis-donnee__texte">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
                            </div>
                        </div>
            <?php endforeach; ?>
        
        </div>
    <?php endif; ?>
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
                                               value="<?= $h['debut'] ?>">
                                        <span class="horaire-sep">→</span>
                                        <input type="time" name="fin[<?= $h['jour'] ?>]"
                                               class="commande-input horaire-input"
                                               value="<?= $h['fin'] ?>">
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
// Onglets principaux
document.querySelectorAll('.compte-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.compte-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.compte-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});
 
// Sous-onglets menus/plats
document.querySelectorAll('.employe-sous-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.employe-sous-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.employe-sous-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.sous).classList.add('active');
    });
});
 
// Toggle formulaires
function toggleForm(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
 
// Annulation employé
function ouvrirAnnulationEmploye(id) {
    document.getElementById('annulation-' + id).style.display = 'block';
}
function fermerAnnulationEmploye(id) {
    document.getElementById('annulation-' + id).style.display = 'none';
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
    const heures = document.getElementById('heures-' + jour);
    const label  = checkbox.closest('.horaire-toggle').querySelector('.horaire-toggle__label');
    if (checkbox.checked) {
        heures.style.opacity = '1';
        heures.style.pointerEvents = 'auto';
        label.textContent = 'Ouvert';
    } else {
        heures.style.opacity = '0.3';
        heures.style.pointerEvents = 'none';
        label.textContent = 'Fermé';
    }
}
</script>
 
<?php require_once '../includes/footer.php'; ?>
 