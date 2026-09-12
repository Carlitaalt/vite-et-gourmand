<?php
use App\Security\Csrf;

/**
 * @var App\Entity\Horaire[] $horaires
 * @var string $currentPage
 */
$page = $currentPage . '.php';
?>
<div class="profil-card">
    <form method="POST" action="<?= $page ?>" class="auth-form">
        <?= Csrf::champ() ?>
        <input type="hidden" name="action" value="update_horaires">

        <h2 class="employe-section-titre mb-1">Horaires d'ouverture</h2>
        <p class="commande-nb-hint mb-3">Ils sont affichés dans le pied de page de toutes les pages du site.</p>

        <div class="horaires-table">
            <?php foreach ($horaires as $horaire):
                $id = $horaire->getId();
                $jour = htmlspecialchars($horaire->getJour());
            ?>
                <div class="horaire-row">
                    <div class="horaire-jour"><?= $jour ?></div>
                    <div class="horaire-toggle">
                        <label class="horaire-switch">
                            <input type="checkbox" name="ouvert[<?= $id ?>]" value="1" class="js-horaire-ouvert" data-cible="heures-<?= $id ?>" <?= $horaire->estOuvert() ? 'checked' : '' ?> aria-label="Ouvert le <?= $jour ?>">
                            <span class="horaire-switch__slider"></span>
                        </label>
                        <span class="horaire-toggle__label" aria-hidden="true"><?= $horaire->estOuvert() ? 'Ouvert' : 'Fermé' ?></span>
                    </div>
                    <div class="horaire-heures" id="heures-<?= $id ?>">
                        <label for="debut-<?= $id ?>" class="visually-hidden">Heure d'ouverture le <?= $jour ?></label>
                        <input type="time" id="debut-<?= $id ?>" name="debut[<?= $id ?>]" class="commande-input horaire-input" value="<?= $horaire->estOuvert() ? $horaire->getOuverture() : '09:00' ?>">
                        <span class="horaire-sep" aria-hidden="true">→</span>
                        <label for="fin-<?= $id ?>" class="visually-hidden">Heure de fermeture le <?= $jour ?></label>
                        <input type="time" id="fin-<?= $id ?>" name="fin[<?= $id ?>]" class="commande-input horaire-input" value="<?= $horaire->estOuvert() ? $horaire->getFermeture() : '18:00' ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="auth-btn mt-3">Enregistrer les horaires</button>
    </form>
</div>
