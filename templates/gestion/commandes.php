<?php
use App\Enum\StatutCommande;
use App\Security\Csrf;

/**
 * @var App\Entity\Commande[] $commandes
 * @var array<string, string> $modesContact
 * @var string $currentPage
 */
$page = $currentPage . '.php';
?>

<div class="employe-filtres" role="search" aria-label="Filtrer les commandes">
    <div class="employe-filtre-group">
        <label for="filtre-statut" class="employe-filtre-label">Filtrer par statut</label>
        <select id="filtre-statut" class="commande-input employe-filtre-select">
            <option value="">Tous les statuts</option>
            <?php foreach (StatutCommande::cases() as $statut): ?>
                <option value="<?= $statut->value ?>"><?= $statut->libelle() ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="employe-filtre-group">
        <label for="filtre-client" class="employe-filtre-label">Rechercher un client</label>
        <input type="search" id="filtre-client" class="commande-input" placeholder="Nom, prénom, e-mail...">
    </div>
</div>

<p class="menus-results-count" aria-live="polite">
    <strong id="nb-commandes-visibles"><?= count($commandes) ?></strong> commande(s)
</p>

<div class="commandes-liste" id="liste-commandes">
    <?php foreach ($commandes as $commande):
        $id = $commande->getId();
        $client = $commande->getClient();
        $statutsSuivants = $commande->getStatutsSuivants();
    ?>
        <article class="commande-item employe-commande-item"
                 data-statut="<?= $commande->getStatut()->value ?>"
                 data-client="<?= htmlspecialchars(mb_strtolower(($client?->getNomComplet() ?? '') . ' ' . ($client?->getEmail() ?? ''))) ?>">

            <div class="commande-item__header">
                <div class="commande-item__id">
                    <h2 class="commande-item__num">Commande n°<?= $id ?></h2>
                    <span class="commande-statut <?= $commande->getStatut()->classeCss() ?>"><?= $commande->getStatut()->libelle() ?></span>
                    <?php if ($commande->aPretMateriel()): ?>
                        <span class="commande-statut statut--materiel">Matériel prêté</span>
                    <?php endif; ?>
                </div>
                <div class="commande-item__prix"><?= number_format($commande->getPrixTotal(), 2, ',', ' ') ?> €</div>
            </div>

            <div class="commande-item__body">
                <div class="commande-item__info">
                    <div class="employe-client-bloc">
                        <div class="employe-client-avatar" aria-hidden="true"><?= htmlspecialchars($client?->getInitiales() ?? '?') ?></div>
                        <div>
                            <div class="employe-client-nom"><?= htmlspecialchars($client?->getNomComplet() ?? 'Client inconnu') ?></div>
                            <div class="employe-client-contact">
                                <a href="mailto:<?= htmlspecialchars($client?->getEmail() ?? '') ?>"><?= htmlspecialchars($client?->getEmail() ?? 'Non renseigné') ?></a>
                                &nbsp;·&nbsp;
                                <?php if ($client?->getTelephone()): ?>
                                    <a href="tel:<?= htmlspecialchars($client->getTelephone()) ?>"><?= htmlspecialchars($client->getTelephone()) ?></a>
                                <?php else: ?>
                                    Téléphone non renseigné
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <dl>
                        <div class="commande-info-row"><dt class="commande-info-label">Menu</dt><dd class="commande-info-val"><?= htmlspecialchars($commande->getMenuTitre()) ?></dd></div>
                        <div class="commande-info-row"><dt class="commande-info-label">Personnes</dt><dd class="commande-info-val"><?= $commande->getNombrePersonnes() ?></dd></div>
                        <div class="commande-info-row"><dt class="commande-info-label">Prestation</dt><dd class="commande-info-val"><?= $commande->getDatePrestation()->format('d/m/Y') ?> à <?= $commande->getHeureLivraison() ?></dd></div>
                        <div class="commande-info-row"><dt class="commande-info-label">Adresse</dt><dd class="commande-info-val"><?= htmlspecialchars($commande->getAdresseLivraison() . ', ' . $commande->getVilleLivraison()) ?></dd></div>
                        <?php if ($commande->getMotifAnnulation()): ?>
                            <div class="commande-info-row"><dt class="commande-info-label">Annulation</dt><dd class="commande-info-val"><?= htmlspecialchars($commande->getMotifAnnulation()) ?><?= $commande->getModeContact() ? ' (contact : ' . htmlspecialchars($modesContact[$commande->getModeContact()] ?? $commande->getModeContact()) . ')' : '' ?></dd></div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="commande-suivi">
                    <h3 class="commande-suivi__titre">Historique</h3>
                    <ol class="suivi-timeline">
                        <?php foreach ($commande->getHistorique() as $etape): ?>
                            <li class="suivi-etape suivi-etape--done">
                                <div class="suivi-etape__dot" aria-hidden="true"></div>
                                <div class="suivi-etape__content">
                                    <span class="suivi-etape__label"><?= $etape->getStatut()->libelle() ?></span>
                                    <span class="suivi-etape__date">le <?= $etape->getDate()->format('d/m à H:i') ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>

            <?php if (!empty($statutsSuivants) || $commande->peutEtreAnnulee()): ?>
                <div class="commande-item__actions employe-commande-actions">
                    <?php if (!empty($statutsSuivants)): ?>
                        <form method="POST" action="<?= $page ?>" class="d-inline-flex gap-2 align-items-center">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="action" value="update_statut">
                            <input type="hidden" name="commande_id" value="<?= $id ?>">
                            <label for="statut-<?= $id ?>" class="visually-hidden">Nouveau statut de la commande n°<?= $id ?></label>
                            <select id="statut-<?= $id ?>" name="nouveau_statut" class="commande-input employe-statut-select">
                                <?php foreach ($statutsSuivants as $statut): ?>
                                    <option value="<?= $statut->value ?>"><?= $statut->libelle() ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-compte-action btn-compte-action--modifier">Mettre à jour →</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($commande->peutEtreAnnulee()): ?>
                        <button type="button" class="btn-compte-action btn-compte-action--annuler" data-bascule="annulation-<?= $id ?>" aria-expanded="false" aria-controls="annulation-<?= $id ?>">
                            ✕ Annuler<span class="visually-hidden"> la commande n°<?= $id ?></span>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($commande->peutEtreAnnulee()): ?>
                    <!-- Annulation : l'employé doit avoir contacté le client et préciser le mode de contact et le motif -->
                    <div class="commande-modif-form employe-annulation-form" id="annulation-<?= $id ?>" hidden>
                        <form method="POST" action="<?= $page ?>" class="modif-form">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="action" value="annuler_commande">
                            <input type="hidden" name="commande_id" value="<?= $id ?>">
                            <h3 class="modif-form__titre">Annuler la commande n°<?= $id ?> — contact client obligatoire</h3>
                            <p class="employe-annulation-notice">Vous devez avoir contacté le client par appel GSM ou par e-mail avant toute annulation. Il sera prévenu par e-mail.</p>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label for="contact-<?= $id ?>" class="commande-label">Mode de contact utilisé <span class="auth-required">*</span></label>
                                    <select id="contact-<?= $id ?>" name="mode_contact" class="commande-input" required>
                                        <option value="">— Sélectionner —</option>
                                        <?php foreach ($modesContact as $valeur => $libelle): ?>
                                            <option value="<?= $valeur ?>"><?= $libelle ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="commande-field">
                                <label for="motif-<?= $id ?>" class="commande-label">Motif de l'annulation <span class="auth-required">*</span></label>
                                <textarea id="motif-<?= $id ?>" name="motif_annulation" class="commande-input avis-textarea" rows="3" required></textarea>
                            </div>
                            <div class="modif-form__actions">
                                <button type="submit" class="btn btn-vg-primary">Confirmer l'annulation</button>
                                <button type="button" class="btn btn-vg-secondary" data-bascule="annulation-<?= $id ?>">Retour</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>

<div class="compte-empty" id="aucune-commande" <?= empty($commandes) ? '' : 'hidden' ?>>
    <h2>Aucune commande trouvée</h2>
    <p>Modifiez vos filtres pour voir d'autres commandes.</p>
</div>
