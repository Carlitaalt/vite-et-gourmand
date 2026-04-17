<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
exiger_connexion();

$pageTitle = 'Mon Compte';
$rootPath = '../';
$currentPage = 'mon-compte';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$estConnecte = isset($_SESSION['user_id']);

//if(!$estConnecte) {
 //   header('Location: ' . $rootPath . 'pages/connexion.php?redirect=mon-compte');
   // exit;
//}

//Données utilisateurs
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

//Si l'utilisateur n'existe pas
if(!$user) {
    header('Location: ' . $rootPath . 'actions/deconnexion.php');
    exit;
}


//Données fictives commandes
$commandes = [
    [
    'commande_id' => 1001,
    'menu_titre' => 'Le Grand Festin de Noël',
    'nb_personnes' => 12,
    'date_prestation' => '2026-12-24',
    'heure_prestation' => '19:00',
    'adresse_prestation' => '5 allée des Pins, 33000 Bordeaux',
    'prix_total' => 384.00,
    'statut' => 'en_attente',
    'created_at' => '2026-11-10 14:32:00',
    'historique' => [
        ['statut' => 'en_attente', 'date' => '2026-11-10 14:32:00'],
    ],
    'avis' => null,
    ],
    [
    'commande_id' => 1002,
    'menu_titre' => 'Menu Prestige Classique',
    'nb_personnes' => 20,
    'date_prestation' => '2026-09-15',
    'heure_prestation' => '12:30',
    'adresse_prestation' => '18 rue du Château, 33100 Bordeaux',
    'prix_total' => 450.00,
    'statut' => 'terminee',
    'created_at' => '2026-08-01 10:00:00',
    'historique' => [
        ['statut' => 'en_attente', 'date' => '2026-08-01 10:00:00'],
        ['statut' => 'accepte', 'date' => '2026-08-02 09:15:00'],
        ['statut' => 'en_cours_de_livraison', 'date' => '2026-09-15 11:00:00'],
        ['statut' => 'livre', 'date' => '2026-09-15 12:20:00'],
        ['statut' => 'terminee', 'date' => '2026-09-15 12:20:00'],
    ],
    'avis' => null,
    ],
    [
    'commande_id' => 1003,
    'menu_titre' => 'Printemps & Pâques',
    'nb_personnes' => 8,
    'date_prestation' => '2026-04-20',
    'heure_prestation' => '13:00',
    'adresse_prestation' => '3 impasse des Lilas, 33200 Bordeaux',
    'prix_total' => 180.00,
    'statut' => 'terminee',
    'created_at' => '2026-03-15 16:45:00',
    'historique' => [
        ['statut' => 'en_attente',
        'date' => '2026-03-15 16:45:00'],
        ['statut' => 'accepte',
        'date' => '2026-03-16 10:00:00'],
        ['statut' => 'en_preparation',
        'date' => '2026-04-19 14:00:00'],
        ['statut' => 'terminee',
        'date' => '2026-04-20 13:10:00'],
    ],
    'avis' => ['note' => 5, 'commentaire' => 'Excellent service, tout était parfait !'],
    ],

];

//Séparer commandes en cours et historique
$commandesEnCours = array_filter($commandes, fn($c) => !in_array($c['statut'], ['terminee', 'annulee']));
$commandesHistorique = array_filter($commandes, fn($c) => in_array($c['statut'], ['terminee', 'annulee']));

//Labels et couleurs des statuts
$statutLabels = [
    'en_attente' => 'En attente',
    'accepte' => 'Acceptée',
    'en_preparation' => 'En préparation',
    'en_cours_de_livraison' => 'En cours de livraison',
    'livre' => 'Livrée',
    'en_attente_materiel' => 'Retour matériel',
    'terminee' => 'Terminée',
    'annulée' => 'Annulée',
];

$statutColors = [
    'en_attente' => 'statut--attente',
    'accepte' => 'statut--accepte',
    'en_preparation' => 'statut--prep',
    'en_cours_de_livraison' => 'statut--livraison',
    'livre' => 'statut--livre',
    'en_attente-materiel' => 'statut--materiel',
    'terminee' => 'statut--termine',
    'annulee' => 'statut--annule',
];

$erreur = '';
$succes = '';

//Traitement formulaire infos
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if($_POST['action'] === 'update_infos') {
        $update = $pdo->prepare("
        UPDATE utilisateur
        SET prenom = :prenom, nom = :nom, email = :email, telephone = :tel, adresse_postale = :adresse, ville = :ville, pays = :pays
        WHERE utilisateur_id = :id
        ");
        $update->execute([
            ':prenom' => $_POST['prenom'],
            ':nom' => $_POST['nom'],
            ':email' => $_POST['email'],
            ':tel' => $_POST['telephone'],
            ':adresse' => $_POST['adresse'],
            ':ville' => $_POST['ville'],
            ':pays' => $_POST['pays'],
            ':id' => $_SESSION['user_id']
        ]);

        //Mise à jour de la session pour la navbar
        $_SESSION['user_prenom'] = $_POST['prenom'];

        $succes = 'Vos informations ont été mise à jour.';

        //Recharge les infos pour l'affichage
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
    } elseif ($_POST['action'] === 'annuler_commande') {
        $succes = 'Commande #' . (int)$_POST['commande_id'] . 'annulée.';
    } elseif ($_POST['action'] === 'modifier_commande') {
        $succes = 'Commande modifiée avec succès.';
    } elseif ($_POST['action'] === 'donner_avis') {
        $succes = 'Votre avis a été soumis et sera visible après validation.';
    } elseif ($_POST['action'] === 'supprimer_compte') {
        try {
            //Suppression dans la base de données
            $delete = $pdo->prepare("DELETE FROM utilisateur WHERE utilisateur_id = :id");
            $delete->execute([':id' => $_SESSION['user_id']]);

            //Nettoyage session
            $_SESSION = [];
            session_destroy();

            //Redirection vers l'accueil avec message
            header('Location: ' . $rootPath . 'pages/accueil.php?msg=compte_supprime');
            exit;
        } catch(PDOException $e) {
            $erreur = "Impossible de supprimer le compte. Il est possible que vous ayez des commandes liées à ce compte.";
        }
    }
}
?>

<section class="section-compte">
    <div class="auth-bg-deco"></div>
    <div class="container">

    <!-- En-tête profil -->
     <div class="compte-header">
        <div class="compte-avatar">
            <?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?>
        </div>
        <div class="compte-header__info">
            <h1 class="compte-header__nom">
                <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
            </h1>
            <p class="compte-header__email">
                <?= htmlspecialchars($user['email']) ?>
            </p>
        </div>
     </div>

     <!-- Alertes -->
      <?php if($succes): ?>
        <div class="auth-alert auth-alert--success mb-3">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($succes) ?>
        </div>
        <?php endif; ?>

        <!-- Onglets -->
         <div class="compte-tabs">
            <button class="compte-tab active" data-tab="en-cours">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Commandes en cours
                <?php if (count($commandesEnCours) > 0) : ?>
                    <span class="compte-tab__badge">
                        <?= count($commandesEnCours) ?>
                    </span>
                    <?php endif; ?>
            </button>
            <button class="compte-tab" data-tab="historique">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Historique
            </button>
            <button class="compte-tab" data-tab="avis">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Mes avis
            </button>
            <button class="compte-tab" data-tab="infos">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Mon profil
            </button>
         </div>

         <!-- CONTENU ONGLETS -->
          <div class="compte-tabs-content">

          <!-- ONGLET : COMMANDE EN COURS -->
           <div class="compte-panel active" id="tab-en-cours">
            <?php if(empty($commandesEnCours)): ?>
                <div class="compte-empty">
                    <h3>Aucune commande en cours</h3>
                    <p>Vous n'avez pas de commande active pour le moment.</p>
                    <a href="<?= $rootPath ?>menus.php" class="btn btn-vg-primary mt-3">Découvrir nos menus</a>
                </div>
                <?php else: ?>
                    <div class="commandes-liste">
                        <?php foreach($commandesEnCours as $cmd): ?>
                            <div class="commande-item" id="commande-<?= $cmd['commande_id'] ?>">
                                <div class="commande-item__header">
                                    <div class="commande-item__id">
                                        <span class="commande-item__num">Commande #<?= $cmd['commande_id'] ?></span>
                                        <span class="commande-statut <?= $statutColors[$cmd['statut']] ?? '' ?>">
                                            <?= $statutLabels[$cmd['statut']] ?? $cmd['statut'] ?>
                                        </span>
                                    </div>
                                    <div class="commande-item__prix"><?= number_format($cmd['prix_total'], 2, ',', ' ') ?> €</div>
                                </div>

                                <div class="commande-item__body">
                                    <div class="commande-item__info">
                                        <div class="commande-info-row">
                                            <span class="commande-info-label">Menu</span>
                                            <span class="commande-info-val"><?= htmlspecialchars($cmd['menu_titre']) ?></span>
                                        </div>
                                        <div class="commande-info-row">
                                            <span class="commande-info-label">Personnes</span>
                                            <span class="commande-info-val"><?= $cmd['nb_personnes'] ?>pers.</span>
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

                                    <!-- SUIVI COMMANDE -->
                                     <div class="commande-suivi">
                                        <h4 class="commande-suivi__titre">Suivi de commande</h4>
                                        <ul class="suivi-timeline">
                                            <?php foreach($cmd['historique'] as $etape): ?>
                                                <li class="suivi-etape suivi-etape--done">
                                                    <div class="suivi-etape--dot"></div>
                                                    <div class="suivi-etape__content">
                                                        <span class="suivi-etape__label"><?= $statutLabels[$etape['statut']] ?? $etape['statut'] ?></span>
                                                        <span class="suivi-etape__date"><?= date('d/m/Y H:i', strtotime($etape['date'])) ?></span>
                                                    </div>
                                                </li>
                                                <?php endforeach; ?>
                                        </ul>
                                     </div>
                                </div>

                                <!-- si attente -->
                                 <?php if($cmd['statut'] === 'en_attente'): ?>
                                    <div class="commande-item__actions">
                                        <!-- Modifier -->
                                         <button class="btn-compte-action btn-compte-action--modifier" onclick="ouvrirModification(<?= $cmd['commande_id'] ?>">
                                            Modifier
                                         </button>
                                         <!-- Annuler -->
                                          <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Confirmer l\'annulation de cette commande?')">
                                            <input type="hidden" name="action" value="annuler_commande">
                                            <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                            <button type="submit" class="btn-compte-action btn-compte-action--annuler">
                                                ✕ Annuler
                                            </button>
                                          </form>
                                    </div>

                                    <!-- Formulaire modification -->
                                     <div class="commande-modif-form" id="modif-<?= $cmd['commande_id'] ?>" style="display: none;">
                                        <form method="POST" action="" class="modif-form">
                                            <input type="hidden" name="action" value="modifier_commande">
                                            <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                            <h4 class="modif-form__titre">Modifier la commande</h4>
                                            <div class="commande-field-row">
                                                <div class="commande-field">
                                                    <label for="" class="commande-label">Nombre de personnes</label>
                                                    <input type="number" name="nb_personnes" class="commande-input" value="<?= $cmd['nb_personnes'] ?>" min="1">
                                                </div>
                                                <div class="commande-field">
                                                    <label for="" class="commande-label">Date de prestation</label>
                                                    <input type="date" name="date_prestation" class="commande-input" value="<?= $cmd['date_prestation'] ?>">
                                                </div>
                                            </div>
                                            <div class="commande-field-row">
                                                <div class="commande-field">
                                                    <label for="" class="commande-label">Heure</label>
                                                    <input type="time" name="heure_prestation" class="commande-input" value="<?= $cmd['heure_prestation'] ?>">
                                                </div>
                                                <div class="commande-field">
                                                    <label for="" class="commande-label">Adresse de livraison</label>
                                                    <input type="text" name="adresse_prestation" class="commande-input" value="<?= htmlspecialchars($cmd['adresse_prestation']) ?>">
                                                </div>
                                            </div>
                                            <div class="modif-form__actions">
                                                <button type="submit" class="btn btn-vg-primary">Enregistrer</button>
                                                <button type="button" class="btn btn-vg-secondary" onclick="fermerModification(<?= $cmd['commande_id'] ?>)">Annuler</button>
                                            </div>
                                        </form>
                                     </div>
                                     <?php endif; ?>

                            </div>
                            <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
           </div>

           <!-- ONGLET HISTORIQUE -->
            <div class="compte-panel" id="tab-historique">
                <?php if(empty($commandesHistorique)): ?>
                    <div class="compte-empty">
                        <h3>Aucun historique</h3>
                        <p>Vos commandes passées apparaîtront ici.</p>
                    </div>
                    <?php else: ?>
                        <div class="commandes-liste">
                            <?php foreach($commandesHistorique as $cmd): ?>
                                <div class="commande-item commande-item--archive">
                                    <div class="commande-item__header">
                                        <div class="commande-item__id">
                                            <span class="commande-item__num">Commande #<?= $cmd['commande_id'] ?></span>
                                            <span class="commande-statut <?= $statutColors[$cmd['statut']] ?? '' ?>">
                                                <?= $statutLabels[$cmd['statut']] ?? $cmd['statut'] ?>
                                            </span>
                                        </div>
                                        <div class="commande-item__prix"><?= number_format($cmd['prix_total'], 2, ',', ' ') ?> €</div>
                                    </div>
                                    <div class="commande-item__body">
                                        <div class="commande-item__info">
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
                                        <!-- Timeline historique -->
                                         <div class="commande-suivi">
                                            <h4 class="commande-suivi__titre">Historique des status</h4>
                                            <ul class="suivi-timeline">
                                                <?php foreach ($cmd['historique'] as $etape): ?>
                                                    <li class="suivi-etape suivi-etape--done">
                                                        <div class="suivi-etape__dot"></div>
                                                        <div class="suivi-etape__content">
                                                            <span class="suivi-etape__label"><?= $statutLabels[$etape['statut']] ?? $etape['statut'] ?></span>
                                                            <span class="suivi-etape__date"><?= date('d/m/Y H:i', strtotime($etape['date'])) ?></span>
                                                        </div>
                                                    </li>
                                                    <?php endforeach; ?>
                                            </ul>
                                         </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
            </div>

            <!-- ONGLET : AVIS -->
             <div class="compte-panel" id="tab-avis">

             <?php
             $commandesAvis = array_filter($commandes, fn($c) => $c['statut'] === 'terminee');
             
             if(empty($commandesAvis)): ?>
             <div class="compte-empty">
                <h3>Aucun avis à donner</h3>
                <p>Vous pourrez donner votre avis une fois une commande terminée.</p>
             </div>
             <?php else: ?>
                <div class="commandes-liste">
                    <?php foreach ($commandesAvis as $cmd): ?>
                        <div class="commande-item">
                            <div class="commande-item__header">
                                <span class="commande-item__num"><?= htmlspecialchars($cmd['menu_titre']) ?></span>
                                <span class="commande-info-label"><?= date('d/m/Y', strtotime($cmd['date_prestation'])) ?></span>
                            </div>

                            <?php if($cmd['avis']): ?>
                                <!-- Avis déjà donnée -->
                                 <div class="avis-donne">
                                    <div class="avis-donne__stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <span class="<?= $i <= $cmd['avis']['note'] ? 'star--on' : 'star--off' ?>">*</span>
                                            <?php endfor; ?>
                                    </div>
                                    <p class="avis-donne__texte"><?= htmlspecialchars($cmd['avis']['commentaire']) ?></p>
                                    <span class="avis-donne__label">Avis soumis - en attente de validation</span>
                                 </div>
                                 <?php else: ?>
                                    <!-- Formulaire avis -->
                                     <form method="POST" action="" class="avis-form">
                                        <input type="hidden" name="action" value="donner_avis">
                                        <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                        <div class="avis-form__stars" id="stars-<?= $cmd['commande_id'] ?>">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <label for="" class="star-label">
                                                    <input type="radio" name="note" value="<?= $i ?>" required>
                                                    <span class="star-icon">★</span>
                                                </label>
                                                <?php endfor; ?>
                                        </div>
                                        <div class="commande-field">
                                            <label for="" class="commande-label">Votre commentaire</label>
                                            <textarea name="commentaire" class="commande-input avis-textarea" placeholder="Partagez votre expérience..." rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-vg-primary mt-2">Envoyer mon avis →</button>
                                     </form>
                                     <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                </div>
                <?php endif; ?>
             </div>

             <!-- ONGLET : MON PROFIL -->
              <div class="compte-panel" id="tab-infos">
                <div class="profil-card">
                    <form method="POST" action="" class="auth-form">
                        <input type="hidden" name="action" value="update_infos">
                        <div class="auth-field-row">
                            <div class="auth-field">
                                <label for="" class="auth-label">Prénom</label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <input type="text" name="prenom" class="auth-input" value="<?= htmlspecialchars($user['prenom']) ?>">
                                </div>
                            </div>
                            <div class="auth-field">
                                <label for="" class="auth-label">Nom</label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <input type="text" name="nom" class="auth-input" value="<?= htmlspecialchars($user['nom']) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label for="" class="auth-label">Adresse e-mail</label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                <input type="email" name="email" class="auth-input" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                        </div>
                        <div class="auth-field-row">
                            <div class="auth-field">
                                <label for="" class="auth-label">Téléphone</label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></svg>
                                    <input type="tel" name="telephone" class="auth-input" value="<?= htmlspecialchars($user['telephone']) ?>">
                                </div>
                            </div>
                            <div class="auth-field">
                                <label for="" class="auth-label">Adresse postale</label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <input type="text" name="adresse" class="auth-input" value="<?= htmlspecialchars($user['adresse_postale']) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="auth-field-row">
                            <div class="auth-field">
                                <label class="auth-label">Ville</label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg>
                                    <input type="text" name="ville" class="auth-input" value="<?= htmlspecialchars($user['ville'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="auth-field">
                                <label class="auth-label">Pays</label>
                                <div class="auth-input-wrap">
                                    <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    <input type="text" name="pays" class="auth-input" value="<?= htmlspecialchars($user['pays'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="auth-btn">
                            Enregistrez les modifications
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                    </form>
                    <form method="POST" action="" onsubmit="return confirm('Attention ! Cette action est irréversible.\nVoulez-vous vraiment supprimer votre compte ?');">
                        <input type="hidden" name="action" value="supprimer_compte">
                            <button type="submit" class="btn-danger">
                                Supprimer mon compte
                            </button>
                    </form>
                </div>
              </div>

          </div>
          <!-- FIN TABS-CONTENT -->
    </div>
</section>

<script>
// Onglets (inchangé)
document.querySelectorAll('.compte-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.compte-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.compte-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

// Modif forms (inchangé)
function ouvrirModification(id) { document.getElementById('modif-' + id).style.display = 'block'; }
function fermerModification(id) { document.getElementById('modif-' + id).style.display = 'none'; }

// ÉTOILES - VERSION PERSISTANTE ✅
document.querySelectorAll('.avis-form__stars').forEach(container => {
    const labels = container.querySelectorAll('.star-label');
    const inputs = container.querySelectorAll('input[type="radio"]');
    
    function updateStars(activeIndex = -1) {
        labels.forEach((label, i) => {
            const icon = label.querySelector('.star-icon');
            if (i <= activeIndex) {
                icon.style.color = 'var(--or)';
                icon.classList.add('star-active');
            } else {
                icon.style.color = '';
                icon.classList.remove('star-active');
            }
        });
    }
    
    // Hover
    labels.forEach((label, i) => {
        label.addEventListener('mouseenter', () => updateStars(i));
    });
    
    // Click
    inputs.forEach((input, i) => {
        input.addEventListener('change', () => {
            const value = parseInt(input.value);
            updateStars(value - 1);
        });
    });
    
    // Leave - garde sélection
    container.addEventListener('mouseleave', () => {
        const checked = container.querySelector('input:checked');
        if (checked) updateStars(parseInt(checked.value) - 1);
    });
});
</script>



<?php require_once '../includes/footer.php'; ?>