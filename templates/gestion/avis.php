<?php
use App\Core\View;
use App\Enum\StatutAvis;

/**
 * Modération des avis. Avec JavaScript, les boutons Publier / Refuser appellent api/avis.php en fetch
 * (sans rechargement) ; sans JavaScript, les formulaires sont envoyés normalement.
 *
 * @var App\Entity\Avis[] $avis
 * @var string $currentPage
 */
$page = $currentPage . '.php';
$enAttente = array_filter($avis, fn($a) => $a->getStatut() === StatutAvis::EnAttente);
$traites = array_filter($avis, fn($a) => $a->getStatut() !== StatutAvis::EnAttente);
?>

<h2 class="employe-section-titre">À valider (<span data-compteur="avis-attente"><?= count($enAttente) ?></span>)</h2>
<div class="commandes-liste" id="liste-avis-attente">
    <?php foreach ($enAttente as $unAvis): ?>
        <?php View::partial('gestion/carte-avis', ['unAvis' => $unAvis, 'page' => $page]); ?>
    <?php endforeach; ?>
</div>
<div class="compte-empty" id="avis-attente-vide" <?= empty($enAttente) ? '' : 'hidden' ?>>
    <p>Aucun avis en attente de validation.</p>
</div>

<h2 class="employe-section-titre mt-5">Avis traités</h2>
<div class="commandes-liste" id="liste-avis-traites">
    <?php foreach ($traites as $unAvis): ?>
        <?php View::partial('gestion/carte-avis', ['unAvis' => $unAvis, 'page' => $page]); ?>
    <?php endforeach; ?>
</div>
<div class="compte-empty" id="avis-traites-vide" <?= empty($traites) ? '' : 'hidden' ?>>
    <p>Aucun avis traité pour le moment.</p>
</div>
