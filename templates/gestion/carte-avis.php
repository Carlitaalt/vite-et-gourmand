<?php
use App\Enum\StatutAvis;
use App\Security\Csrf;

/**
 * @var App\Entity\Avis $unAvis
 * @var string $page
 */
$auteur = trim(($unAvis->getAuteurPrenom() ?? '') . ' ' . ($unAvis->getAuteurNom() ?? ''));
?>
<article class="commande-item" data-avis-id="<?= $unAvis->getId() ?>">
    <div class="commande-item__header">
        <div class="commande-item__id">
            <h3 class="commande-item__num"><?= htmlspecialchars($auteur ?: 'Client') ?></h3>
            <span class="commande-statut js-statut-avis <?= $unAvis->getStatut()->classeCss() ?>"><?= $unAvis->getStatut()->libelle() ?></span>
        </div>
        <span class="commande-info-label"><?= $unAvis->getCreeLe()?->format('d/m/Y') ?></span>
    </div>

    <div class="avis-employe-body">
        <div class="avis-employe-menu"><?= htmlspecialchars($unAvis->getMenuTitre() ?? '') ?></div>
        <div class="avis-donne__stars" role="img" aria-label="Note : <?= $unAvis->getNote() ?> sur 5">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="<?= $i <= $unAvis->getNote() ? 'star--on' : 'star--off' ?>" aria-hidden="true">★</span>
            <?php endfor; ?>
        </div>
        <p class="avis-donne__texte">« <?= htmlspecialchars($unAvis->getCommentaire()) ?> »</p>
    </div>

    <?php if ($unAvis->getStatut() === StatutAvis::EnAttente): ?>
        <div class="commande-item__actions js-actions-avis">
            <form method="POST" action="<?= $page ?>" class="d-inline js-moderation" data-decision="publier">
                <?= Csrf::champ() ?>
                <input type="hidden" name="action" value="valider_avis">
                <input type="hidden" name="avis_id" value="<?= $unAvis->getId() ?>">
                <button type="submit" class="btn-compte-action btn-compte-action--modifier">✓ Publier</button>
            </form>
            <form method="POST" action="<?= $page ?>" class="d-inline js-moderation" data-decision="refuser">
                <?= Csrf::champ() ?>
                <input type="hidden" name="action" value="refuser_avis">
                <input type="hidden" name="avis_id" value="<?= $unAvis->getId() ?>">
                <button type="submit" class="btn-compte-action btn-compte-action--annuler">✕ Refuser</button>
            </form>
        </div>
    <?php endif; ?>
</article>
