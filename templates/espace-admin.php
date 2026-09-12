<?php
use App\Core\View;

/**
 * L'administrateur a ses propres onglets (employés, statistiques) puis tous ceux de l'employé,
 * qui réutilisent les mêmes templates (dossier templates/gestion/).
 */
$donnees = get_defined_vars();
?>

<section class="section-compte section-employe section-admin">
    <div class="auth-bg-deco"></div>
    <div class="container">

        <?php View::partial('gestion/entete', $donnees); ?>

        <div class="compte-tabs" role="tablist" aria-label="Espace administrateur">
            <button type="button" class="compte-tab active" role="tab" id="onglet-employes" data-tab="employes" aria-controls="tab-employes" aria-selected="true">
                Employés <span class="compte-tab__badge admin-badge"><?= count($employes) ?></span>
            </button>
            <button type="button" class="compte-tab" role="tab" id="onglet-statistiques" data-tab="statistiques" aria-controls="tab-statistiques" aria-selected="false">Statistiques & CA</button>
            <button type="button" class="compte-tab" role="tab" id="onglet-commandes" data-tab="commandes" aria-controls="tab-commandes" aria-selected="false">
                Commandes
                <?php if ($nbEnAttente > 0): ?><span class="compte-tab__badge"><?= $nbEnAttente ?></span><?php endif; ?>
            </button>
            <button type="button" class="compte-tab" role="tab" id="onglet-avis-employe" data-tab="avis-employe" aria-controls="tab-avis-employe" aria-selected="false">
                Avis clients
                <span class="compte-tab__badge" data-compteur="avis-attente" <?= $nbAvisAttente > 0 ? '' : 'hidden' ?>><?= $nbAvisAttente ?></span>
            </button>
            <button type="button" class="compte-tab" role="tab" id="onglet-menus" data-tab="menus" aria-controls="tab-menus" aria-selected="false">Menus & plats</button>
            <button type="button" class="compte-tab" role="tab" id="onglet-horaires" data-tab="horaires" aria-controls="tab-horaires" aria-selected="false">Horaires</button>
        </div>

        <div class="compte-tabs-content">
            <div class="compte-panel active" id="tab-employes" role="tabpanel" aria-labelledby="onglet-employes">
                <?php View::partial('admin/employes', $donnees); ?>
            </div>
            <div class="compte-panel" id="tab-statistiques" role="tabpanel" aria-labelledby="onglet-statistiques">
                <?php View::partial('admin/statistiques', $donnees); ?>
            </div>
            <div class="compte-panel" id="tab-commandes" role="tabpanel" aria-labelledby="onglet-commandes">
                <?php View::partial('gestion/commandes', $donnees); ?>
            </div>
            <div class="compte-panel" id="tab-avis-employe" role="tabpanel" aria-labelledby="onglet-avis-employe">
                <?php View::partial('gestion/avis', $donnees); ?>
            </div>
            <div class="compte-panel" id="tab-menus" role="tabpanel" aria-labelledby="onglet-menus">
                <?php View::partial('gestion/menus-plats', $donnees); ?>
            </div>
            <div class="compte-panel" id="tab-horaires" role="tabpanel" aria-labelledby="onglet-horaires">
                <?php View::partial('gestion/horaires', $donnees); ?>
            </div>
        </div>
    </div>
</section>
