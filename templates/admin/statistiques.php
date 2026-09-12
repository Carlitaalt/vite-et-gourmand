<?php
/**
 * Statistiques de l'administrateur. Les premières données sont intégrées à la page (JSON),
 * puis chaque changement de filtre les recharge par fetch (api/statistiques.php).
 *
 * @var App\Entity\StatistiqueMenu[] $commandesParMenu  (MongoDB)
 * @var array{total: float, nombreCommandes: int, parMenu: App\Entity\StatistiqueMenu[]} $chiffreAffaires  (MySQL)
 * @var App\Entity\Menu[] $menus
 */
?>
<div id="zone-statistiques">

    <form class="admin-stats-filtres" id="filtres-statistiques" role="search" aria-label="Filtrer les statistiques">
        <div class="employe-filtre-group">
            <label for="stat-menu" class="employe-filtre-label">Menu</label>
            <select id="stat-menu" name="menu" class="commande-input">
                <option value="">Tous les menus</option>
                <?php foreach ($menus as $menu): ?>
                    <option value="<?= $menu->getId() ?>"><?= htmlspecialchars($menu->getTitre()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="employe-filtre-group">
            <label for="stat-debut" class="employe-filtre-label">Du</label>
            <input type="date" id="stat-debut" name="debut" class="commande-input">
        </div>
        <div class="employe-filtre-group">
            <label for="stat-fin" class="employe-filtre-label">Au</label>
            <input type="date" id="stat-fin" name="fin" class="commande-input">
        </div>
        <div class="employe-filtre-group align-self-end">
            <button type="reset" class="btn-compte-action btn-compte-action--modifier">Réinitialiser</button>
        </div>
    </form>

    <div class="admin-ca-resultat">
        <div class="admin-ca-card" aria-live="polite">
            <div class="admin-ca-card__label">Chiffre d'affaires (commandes terminées)</div>
            <div class="admin-ca-card__val" id="ca-total"><?= number_format($chiffreAffaires['total'], 2, ',', ' ') ?> €</div>
            <div class="admin-ca-card__sub" id="ca-nombre"><?= $chiffreAffaires['nombreCommandes'] ?> commande(s) terminée(s)</div>
        </div>
    </div>

    <div class="commande-bloc">
        <div class="bloc-header" style="padding:1rem 1.25rem; border-bottom:1px solid var(--creme-fonce);">
            <h2 class="employe-section-titre m-0">Nombre de commandes par menu</h2>
            <div class="admin-chart-toggle" role="group" aria-label="Type de graphique">
                <button type="button" class="admin-chart-btn active" data-type="bar" aria-pressed="true">Barres</button>
                <button type="button" class="admin-chart-btn" data-type="pie" aria-pressed="false">Camembert</button>
            </div>
        </div>
        <div style="padding:1.5rem;">
            <div class="graphique-conteneur">
                <canvas id="graphique-menus" role="img" aria-label="Graphique du nombre de commandes par menu. Le détail chiffré figure dans le tableau récapitulatif."></canvas>
            </div>
            <p class="stat-source">Source : base NoSQL MongoDB (collection commandes_stats), alimentée à chaque commande passée.</p>
        </div>
    </div>

    <div class="commande-bloc mt-4">
        <div class="bloc-header" style="padding:1rem 1.25rem; border-bottom:1px solid var(--creme-fonce);">
            <h2 class="employe-section-titre m-0">Récapitulatif par menu</h2>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <caption class="visually-hidden">Nombre de commandes et chiffre d'affaires par menu</caption>
                <thead>
                    <tr>
                        <th scope="col">Menu</th>
                        <th scope="col">Commandes passées</th>
                        <th scope="col">CA des commandes terminées</th>
                        <th scope="col">Part du CA</th>
                    </tr>
                </thead>
                <tbody id="tableau-statistiques"></tbody>
            </table>
        </div>
    </div>

    <!-- Données initiales (JSON_HEX_TAG empêche de fermer la balise script avec un titre de menu malveillant) -->
    <script type="application/json" id="donnees-statistiques"><?= json_encode(
        ['commandesParMenu' => $commandesParMenu, 'chiffreAffaires' => $chiffreAffaires],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
    ) ?></script>
</div>
