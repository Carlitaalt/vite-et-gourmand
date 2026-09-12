<?php
use App\Security\Csrf;

/**
 * @var App\Entity\Utilisateur $utilisateur
 * @var App\Entity\Commande[] $commandesEnCours
 * @var App\Entity\Commande[] $commandesHistorique
 * @var App\Entity\Commande[] $commandesTerminees
 */
$demain = date('Y-m-d', strtotime('+1 day'));
?>

<section class="section-compte">
    <div class="auth-bg-deco"></div>
    <div class="container">

        <div class="compte-header">
            <div class="compte-avatar" aria-hidden="true"><?= htmlspecialchars($utilisateur->getInitiales()) ?></div>
            <div class="compte-header__info">
                <h1 class="compte-header__nom"><?= htmlspecialchars($utilisateur->getNomComplet()) ?></h1>
                <p class="compte-header__email"><?= htmlspecialchars($utilisateur->getEmail()) ?></p>
            </div>
        </div>

        <!-- Onglets (gérés par assets/js/main.js, accessibles au clavier) -->
        <div class="compte-tabs" role="tablist" aria-label="Mon espace">
            <button type="button" class="compte-tab active" role="tab" id="onglet-en-cours" data-tab="en-cours" aria-controls="tab-en-cours" aria-selected="true">
                Commandes en cours
                <?php if (count($commandesEnCours) > 0): ?>
                    <span class="compte-tab__badge"><?= count($commandesEnCours) ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="compte-tab" role="tab" id="onglet-historique" data-tab="historique" aria-controls="tab-historique" aria-selected="false">Historique</button>
            <button type="button" class="compte-tab" role="tab" id="onglet-avis" data-tab="avis" aria-controls="tab-avis" aria-selected="false">Mes avis</button>
            <button type="button" class="compte-tab" role="tab" id="onglet-infos" data-tab="infos" aria-controls="tab-infos" aria-selected="false">Mon profil</button>
        </div>

        <div class="compte-tabs-content">

            <!-- COMMANDES EN COURS -->
            <div class="compte-panel active" id="tab-en-cours" role="tabpanel" aria-labelledby="onglet-en-cours">
                <?php if (empty($commandesEnCours)): ?>
                    <div class="compte-empty">
                        <h2>Aucune commande en cours</h2>
                        <p>Vous n'avez pas de commande active pour le moment.</p>
                        <a href="<?= $rootPath ?>pages/menus.php" class="btn btn-vg-primary mt-3">Découvrir nos menus</a>
                    </div>
                <?php else: ?>
                    <div class="commandes-liste">
                        <?php foreach ($commandesEnCours as $commande): $id = $commande->getId(); ?>
                            <article class="commande-item" id="commande-<?= $id ?>">
                                <div class="commande-item__header">
                                    <div class="commande-item__id">
                                        <h2 class="commande-item__num">Commande n°<?= $id ?></h2>
                                        <span class="commande-statut <?= $commande->getStatut()->classeCss() ?>"><?= $commande->getStatut()->libelle() ?></span>
                                    </div>
                                    <div class="commande-item__prix"><?= number_format($commande->getPrixTotal(), 2, ',', ' ') ?> €</div>
                                </div>

                                <div class="commande-item__body">
                                    <dl class="commande-item__info">
                                        <div class="commande-info-row"><dt class="commande-info-label">Menu</dt><dd class="commande-info-val"><?= htmlspecialchars($commande->getMenuTitre()) ?></dd></div>
                                        <div class="commande-info-row"><dt class="commande-info-label">Personnes</dt><dd class="commande-info-val"><?= $commande->getNombrePersonnes() ?></dd></div>
                                        <div class="commande-info-row"><dt class="commande-info-label">Date</dt><dd class="commande-info-val"><?= $commande->getDatePrestation()->format('d/m/Y') ?> à <?= $commande->getHeureLivraison() ?></dd></div>
                                        <div class="commande-info-row"><dt class="commande-info-label">Adresse</dt><dd class="commande-info-val"><?= htmlspecialchars($commande->getAdresseLivraison() . ', ' . $commande->getVilleLivraison()) ?></dd></div>
                                        <div class="commande-info-row"><dt class="commande-info-label">Livraison</dt><dd class="commande-info-val"><?= $commande->getPrixLivraison() > 0 ? number_format($commande->getPrixLivraison(), 2, ',', ' ') . ' €' : 'Offerte' ?></dd></div>
                                        <?php if ($commande->aPretMateriel()): ?>
                                            <div class="commande-info-row"><dt class="commande-info-label">Matériel</dt><dd class="commande-info-val">Prêt de matériel demandé</dd></div>
                                        <?php endif; ?>
                                    </dl>

                                    <div class="commande-suivi">
                                        <h3 class="commande-suivi__titre">Suivi de commande</h3>
                                        <ol class="suivi-timeline">
                                            <?php foreach ($commande->getHistorique() as $etape): ?>
                                                <li class="suivi-etape suivi-etape--done">
                                                    <div class="suivi-etape__dot" aria-hidden="true"></div>
                                                    <div class="suivi-etape__content">
                                                        <span class="suivi-etape__label"><?= $etape->getStatut()->libelle() ?></span>
                                                        <span class="suivi-etape__date">le <?= $etape->getDate()->format('d/m/Y à H:i') ?></span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                </div>

                                <?php if ($commande->estModifiableParClient()): ?>
                                    <div class="commande-item__actions">
                                        <button type="button" class="btn-compte-action btn-compte-action--modifier" data-bascule="modif-<?= $id ?>" aria-expanded="false" aria-controls="modif-<?= $id ?>">Modifier</button>
                                        <form method="POST" action="mon-compte.php" class="d-inline" data-confirmation="Confirmer l'annulation de la commande n°<?= $id ?> ?">
                                            <?= Csrf::champ() ?>
                                            <input type="hidden" name="action" value="annuler_commande">
                                            <input type="hidden" name="commande_id" value="<?= $id ?>">
                                            <button type="submit" class="btn-compte-action btn-compte-action--annuler">✕ Annuler la commande</button>
                                        </form>
                                    </div>

                                    <!-- Modification : tout sauf le menu, tant que la commande n'est pas acceptée -->
                                    <div class="commande-modif-form" id="modif-<?= $id ?>" hidden>
                                        <form method="POST" action="mon-compte.php" class="modif-form">
                                            <?= Csrf::champ() ?>
                                            <input type="hidden" name="action" value="modifier_commande">
                                            <input type="hidden" name="commande_id" value="<?= $id ?>">
                                            <h3 class="modif-form__titre">Modifier la commande n°<?= $id ?></h3>
                                            <p class="commande-nb-hint">Le menu ne peut pas être changé. Le prix sera recalculé (remise et livraison comprises).</p>
                                            <div class="commande-field-row">
                                                <div class="commande-field">
                                                    <label for="nb-<?= $id ?>" class="commande-label">Nombre de personnes</label>
                                                    <input type="number" id="nb-<?= $id ?>" name="nombre_personnes" class="commande-input" min="<?= $commande->getMenu()?->getNombrePersonneMinimum() ?? 1 ?>" value="<?= $commande->getNombrePersonnes() ?>" required>
                                                </div>
                                                <div class="commande-field">
                                                    <label for="date-<?= $id ?>" class="commande-label">Date de prestation</label>
                                                    <input type="date" id="date-<?= $id ?>" name="date_prestation" class="commande-input" min="<?= $demain ?>" value="<?= $commande->getDatePrestation()->format('Y-m-d') ?>" required>
                                                </div>
                                            </div>
                                            <div class="commande-field-row">
                                                <div class="commande-field">
                                                    <label for="heure-<?= $id ?>" class="commande-label">Heure de livraison</label>
                                                    <input type="time" id="heure-<?= $id ?>" name="heure_livraison" class="commande-input" value="<?= $commande->getHeureLivraison() ?>" required>
                                                </div>
                                                <div class="commande-field">
                                                    <label for="adresse-<?= $id ?>" class="commande-label">Adresse de livraison</label>
                                                    <input type="text" id="adresse-<?= $id ?>" name="adresse_livraison" class="commande-input" value="<?= htmlspecialchars($commande->getAdresseLivraison()) ?>" required>
                                                </div>
                                            </div>
                                            <div class="commande-field-row">
                                                <div class="commande-field">
                                                    <label for="ville-<?= $id ?>" class="commande-label">Ville</label>
                                                    <input type="text" id="ville-<?= $id ?>" name="ville_livraison" class="commande-input" value="<?= htmlspecialchars($commande->getVilleLivraison()) ?>" required>
                                                </div>
                                                <div class="commande-field">
                                                    <label for="distance-<?= $id ?>" class="commande-label">Distance depuis Bordeaux (km)</label>
                                                    <input type="number" id="distance-<?= $id ?>" name="distance_km" class="commande-input" min="0" step="0.1" value="<?= $commande->getDistanceKm() ?>">
                                                </div>
                                            </div>
                                            <div class="modif-form__actions">
                                                <button type="submit" class="btn btn-vg-primary">Enregistrer</button>
                                                <button type="button" class="btn btn-vg-secondary" data-bascule="modif-<?= $id ?>">Fermer</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- HISTORIQUE -->
            <div class="compte-panel" id="tab-historique" role="tabpanel" aria-labelledby="onglet-historique">
                <?php if (empty($commandesHistorique)): ?>
                    <div class="compte-empty">
                        <h2>Aucun historique</h2>
                        <p>Vos commandes terminées ou annulées apparaîtront ici.</p>
                    </div>
                <?php else: ?>
                    <div class="commandes-liste">
                        <?php foreach ($commandesHistorique as $commande): ?>
                            <article class="commande-item commande-item--archive">
                                <div class="commande-item__header">
                                    <div class="commande-item__id">
                                        <h2 class="commande-item__num">Commande n°<?= $commande->getId() ?></h2>
                                        <span class="commande-statut <?= $commande->getStatut()->classeCss() ?>"><?= $commande->getStatut()->libelle() ?></span>
                                    </div>
                                    <div class="commande-item__prix"><?= number_format($commande->getPrixTotal(), 2, ',', ' ') ?> €</div>
                                </div>
                                <div class="commande-item__body">
                                    <dl class="commande-item__info">
                                        <div class="commande-info-row"><dt class="commande-info-label">Menu</dt><dd class="commande-info-val"><?= htmlspecialchars($commande->getMenuTitre()) ?></dd></div>
                                        <div class="commande-info-row"><dt class="commande-info-label">Personnes</dt><dd class="commande-info-val"><?= $commande->getNombrePersonnes() ?></dd></div>
                                        <div class="commande-info-row"><dt class="commande-info-label">Date</dt><dd class="commande-info-val"><?= $commande->getDatePrestation()->format('d/m/Y') ?> à <?= $commande->getHeureLivraison() ?></dd></div>
                                        <?php if ($commande->getMotifAnnulation()): ?>
                                            <div class="commande-info-row"><dt class="commande-info-label">Motif d'annulation</dt><dd class="commande-info-val"><?= htmlspecialchars($commande->getMotifAnnulation()) ?></dd></div>
                                        <?php endif; ?>
                                    </dl>
                                    <div class="commande-suivi">
                                        <h3 class="commande-suivi__titre">Historique des statuts</h3>
                                        <ol class="suivi-timeline">
                                            <?php foreach ($commande->getHistorique() as $etape): ?>
                                                <li class="suivi-etape suivi-etape--done">
                                                    <div class="suivi-etape__dot" aria-hidden="true"></div>
                                                    <div class="suivi-etape__content">
                                                        <span class="suivi-etape__label"><?= $etape->getStatut()->libelle() ?></span>
                                                        <span class="suivi-etape__date">le <?= $etape->getDate()->format('d/m/Y à H:i') ?></span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- AVIS : possible uniquement sur les commandes terminées -->
            <div class="compte-panel" id="tab-avis" role="tabpanel" aria-labelledby="onglet-avis">
                <?php if (empty($commandesTerminees)): ?>
                    <div class="compte-empty">
                        <h2>Aucun avis à donner</h2>
                        <p>Vous pourrez donner votre avis dès qu'une commande sera terminée.</p>
                    </div>
                <?php else: ?>
                    <div class="commandes-liste">
                        <?php foreach ($commandesTerminees as $commande): $avis = $commande->getAvis(); ?>
                            <article class="commande-item">
                                <div class="commande-item__header">
                                    <h2 class="commande-item__num"><?= htmlspecialchars($commande->getMenuTitre()) ?></h2>
                                    <span class="commande-info-label">Prestation du <?= $commande->getDatePrestation()->format('d/m/Y') ?></span>
                                </div>

                                <?php if ($avis !== null): ?>
                                    <div class="avis-donne">
                                        <div class="avis-donne__stars" role="img" aria-label="Votre note : <?= $avis->getNote() ?> sur 5">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star-icon <?= $i <= $avis->getNote() ? 'star--on' : 'star--off' ?>" aria-hidden="true">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="avis-donne__texte"><?= htmlspecialchars($avis->getCommentaire()) ?></p>
                                        <p class="avis-donne__label"><span class="commande-statut <?= $avis->getStatut()->classeCss() ?>"><?= $avis->getStatut()->libelle() ?></span></p>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="mon-compte.php" class="avis-form">
                                        <?= Csrf::champ() ?>
                                        <input type="hidden" name="action" value="donner_avis">
                                        <input type="hidden" name="commande_id" value="<?= $commande->getId() ?>">

                                        <fieldset>
                                            <legend class="commande-label">Votre note <span class="auth-required">*</span></legend>
                                            <div class="avis-form__stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <input type="radio" name="note" value="<?= $i ?>" id="note-<?= $commande->getId() ?>-<?= $i ?>" class="star-radio" required>
                                                    <label for="note-<?= $commande->getId() ?>-<?= $i ?>" class="star-label">
                                                        <span class="star-icon" aria-hidden="true">★</span>
                                                        <span class="visually-hidden"><?= $i ?> sur 5</span>
                                                    </label>
                                                <?php endfor; ?>
                                            </div>
                                        </fieldset>

                                        <div class="commande-field mt-3">
                                            <label for="commentaire-<?= $commande->getId() ?>" class="commande-label">Votre commentaire <span class="auth-required">*</span></label>
                                            <textarea id="commentaire-<?= $commande->getId() ?>" name="commentaire" class="commande-input avis-textarea" placeholder="Partagez votre expérience..." rows="3" maxlength="1000" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-vg-primary mt-2">Envoyer mon avis →</button>
                                    </form>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- PROFIL -->
            <div class="compte-panel" id="tab-infos" role="tabpanel" aria-labelledby="onglet-infos">
                <div class="profil-card">
                    <form method="POST" action="mon-compte.php" class="auth-form">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="action" value="update_infos">

                        <div class="auth-field-row">
                            <div class="auth-field">
                                <label for="profil-prenom" class="auth-label">Prénom <span class="auth-required">*</span></label>
                                <input type="text" id="profil-prenom" name="prenom" class="auth-input" value="<?= htmlspecialchars($utilisateur->getPrenom()) ?>" required autocomplete="given-name">
                            </div>
                            <div class="auth-field">
                                <label for="profil-nom" class="auth-label">Nom <span class="auth-required">*</span></label>
                                <input type="text" id="profil-nom" name="nom" class="auth-input" value="<?= htmlspecialchars($utilisateur->getNom()) ?>" required autocomplete="family-name">
                            </div>
                        </div>
                        <div class="auth-field">
                            <label for="profil-email" class="auth-label">Adresse e-mail <span class="auth-required">*</span></label>
                            <input type="email" id="profil-email" name="email" class="auth-input" value="<?= htmlspecialchars($utilisateur->getEmail()) ?>" required autocomplete="email">
                        </div>
                        <div class="auth-field-row">
                            <div class="auth-field">
                                <label for="profil-tel" class="auth-label">Téléphone (GSM)</label>
                                <input type="tel" id="profil-tel" name="telephone" class="auth-input" value="<?= htmlspecialchars($utilisateur->getTelephone() ?? '') ?>" autocomplete="tel">
                            </div>
                            <div class="auth-field">
                                <label for="profil-adresse" class="auth-label">Adresse postale</label>
                                <input type="text" id="profil-adresse" name="adresse" class="auth-input" value="<?= htmlspecialchars($utilisateur->getAdressePostale() ?? '') ?>" autocomplete="street-address">
                            </div>
                        </div>
                        <div class="auth-field-row">
                            <div class="auth-field">
                                <label for="profil-ville" class="auth-label">Ville</label>
                                <input type="text" id="profil-ville" name="ville" class="auth-input" value="<?= htmlspecialchars($utilisateur->getVille() ?? '') ?>" autocomplete="address-level2">
                            </div>
                            <div class="auth-field">
                                <label for="profil-pays" class="auth-label">Pays</label>
                                <input type="text" id="profil-pays" name="pays" class="auth-input" value="<?= htmlspecialchars($utilisateur->getPays() ?? '') ?>" autocomplete="country-name">
                            </div>
                        </div>
                        <button type="submit" class="auth-btn">Enregistrer les modifications</button>
                    </form>

                    <!-- Droit à l'effacement (RGPD) -->
                    <form method="POST" action="mon-compte.php" class="mt-4" data-confirmation="Attention : cette action est irréversible. Voulez-vous vraiment supprimer votre compte ?">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="action" value="supprimer_compte">
                        <button type="submit" class="btn-danger">Supprimer mon compte</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>
