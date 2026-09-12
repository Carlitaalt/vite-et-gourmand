<?php
use App\Security\Csrf;

/**
 * @var App\Entity\Utilisateur $client
 * @var App\Entity\Menu[] $menus
 * @var int $menuPreselectionne
 * @var array $saisie  saisie précédente, en cas d'erreur
 */
$menuChoisi = (int) ($saisie['menu_id'] ?? $menuPreselectionne);
$minimumChoisi = 1;
foreach ($menus as $menu) {
    if ($menu->getId() === $menuChoisi) {
        $minimumChoisi = $menu->getNombrePersonneMinimum();
    }
}
$valeur = fn(string $cle, ?string $defaut = null): string => htmlspecialchars((string) ($saisie[$cle] ?? $defaut ?? ''));
?>

<section class="section-commande">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="commande-header">
            <a href="<?= $rootPath ?>pages/menus.php" class="detail-back">← Retour aux menus</a>
            <div class="text-center mt-3">
                <span class="section-label">Réservation</span>
                <h1 class="section-title">Commander un menu</h1>
                <div class="divider-or mx-auto"></div>
            </div>
        </div>

        <form method="POST" action="commande.php" class="commande-form" id="commande-form" data-racine="<?= $rootPath ?>">
            <?= Csrf::champ() ?>

            <div class="commande-layout">
                <div class="commande-main">

                    <!-- BLOC 1 : informations client, pré-remplies depuis le compte -->
                    <div class="commande-bloc">
                        <div class="commande-bloc__head">
                            <span class="commande-bloc__num" aria-hidden="true">1</span>
                            <h2 class="commande-bloc__title">Vos informations</h2>
                        </div>
                        <div class="commande-bloc__body">
                            <p class="commande-autofill-notice">
                                Ces informations viennent de votre compte. Pour les changer, rendez-vous dans <a href="<?= $rootPath ?>pages/mon-compte.php#infos">Mon profil</a>.
                            </p>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label for="client-prenom" class="commande-label">Prénom</label>
                                    <input type="text" id="client-prenom" class="commande-input" value="<?= htmlspecialchars($client->getPrenom()) ?>" readonly>
                                </div>
                                <div class="commande-field">
                                    <label for="client-nom" class="commande-label">Nom</label>
                                    <input type="text" id="client-nom" class="commande-input" value="<?= htmlspecialchars($client->getNom()) ?>" readonly>
                                </div>
                            </div>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label for="client-email" class="commande-label">E-mail</label>
                                    <input type="email" id="client-email" class="commande-input" value="<?= htmlspecialchars($client->getEmail()) ?>" readonly>
                                </div>
                                <div class="commande-field">
                                    <label for="client-tel" class="commande-label">Téléphone (GSM)</label>
                                    <input type="tel" id="client-tel" class="commande-input" value="<?= htmlspecialchars($client->getTelephone() ?? '') ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOC 2 : menu et nombre de personnes -->
                    <div class="commande-bloc">
                        <div class="commande-bloc__head">
                            <span class="commande-bloc__num" aria-hidden="true">2</span>
                            <h2 class="commande-bloc__title">Choix du menu</h2>
                        </div>
                        <div class="commande-bloc__body">
                            <div class="commande-field">
                                <label for="menu_id" class="commande-label">Menu <span class="auth-required">*</span></label>
                                <select name="menu_id" id="menu_id" class="commande-input commande-select" required>
                                    <option value="">— Sélectionnez un menu —</option>
                                    <?php foreach ($menus as $menu): ?>
                                        <option value="<?= $menu->getId() ?>"
                                                data-titre="<?= htmlspecialchars($menu->getTitre()) ?>"
                                                data-min="<?= $menu->getNombrePersonneMinimum() ?>"
                                                data-conditions="<?= htmlspecialchars($menu->getConditions()) ?>"
                                                <?= $menu->getId() === $menuChoisi ? 'selected' : '' ?>
                                                <?= $menu->estDisponible() ? '' : 'disabled' ?>>
                                            <?= htmlspecialchars($menu->getTitre()) ?> — <?= number_format($menu->getPrixParPersonne(), 2, ',', ' ') ?> € / pers.<?= $menu->estDisponible() ? '' : ' (complet)' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="commande-field">
                                <label for="nb_personnes" class="commande-label">Nombre de personnes <span class="auth-required">*</span></label>
                                <div class="commande-nb-wrap">
                                    <button type="button" class="commande-nb-btn" id="nb-moins" aria-label="Retirer une personne">-</button>
                                    <input type="number" id="nb_personnes" name="nb_personnes" class="commande-input commande-nb-input" value="<?= $valeur('nb_personnes', (string) $minimumChoisi) ?>" min="<?= $minimumChoisi ?>" inputmode="numeric" required aria-describedby="nb-aide">
                                    <button type="button" class="commande-nb-btn" id="nb-plus" aria-label="Ajouter une personne">+</button>
                                </div>
                                <span class="commande-nb-hint" id="nb-aide"></span>
                            </div>
                        </div>
                    </div>

                    <!-- BLOC 3 : lieu, date et heure de la prestation -->
                    <div class="commande-bloc">
                        <div class="commande-bloc__head">
                            <span class="commande-bloc__num" aria-hidden="true">3</span>
                            <h2 class="commande-bloc__title">Lieu & date de la prestation</h2>
                        </div>
                        <div class="commande-bloc__body">
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label for="date_prestation" class="commande-label">Date <span class="auth-required">*</span></label>
                                    <input type="date" id="date_prestation" name="date_prestation" class="commande-input" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= $valeur('date_prestation') ?>">
                                </div>
                                <div class="commande-field">
                                    <label for="heure_prestation" class="commande-label">Heure de livraison souhaitée <span class="auth-required">*</span></label>
                                    <input type="time" id="heure_prestation" name="heure_prestation" class="commande-input" required value="<?= $valeur('heure_prestation') ?>">
                                </div>
                            </div>
                            <div class="commande-field-row">
                                <div class="commande-field">
                                    <label for="adresse_prestation" class="commande-label">Adresse de livraison <span class="auth-required">*</span></label>
                                    <input type="text" id="adresse_prestation" name="adresse_prestation" class="commande-input" placeholder="12 rue des Lilas" required autocomplete="street-address" value="<?= $valeur('adresse_prestation', $client->getAdressePostale()) ?>">
                                </div>
                                <div class="commande-field">
                                    <label for="ville_prestation" class="commande-label">Ville <span class="auth-required">*</span></label>
                                    <input type="text" id="ville_prestation" name="ville_prestation" class="commande-input" placeholder="Bordeaux" required autocomplete="address-level2" value="<?= $valeur('ville_prestation', $client->getVille()) ?>">
                                </div>
                            </div>
                            <div class="commande-field" id="bloc-distance">
                                <label for="distance_km" class="commande-label">Distance depuis Bordeaux (km)</label>
                                <input type="number" id="distance_km" name="distance_km" class="commande-input" min="0" step="0.1" value="<?= $valeur('distance_km', '0') ?>" aria-describedby="distance-aide">
                                <p class="commande-nb-hint" id="distance-aide">Livraison offerte à Bordeaux. Ailleurs : 5 € + 0,59 € par kilomètre.</p>
                            </div>

                            <div class="commande-option-item mt-3">
                                <input type="checkbox" id="pret_materiel" name="pret_materiel" value="1" <?= !empty($saisie['pret_materiel']) ? 'checked' : '' ?> aria-describedby="materiel-aide">
                                <label for="pret_materiel" class="commande-label ms-2">Besoin d'un prêt de matériel (vaisselle, couverts, tables...)</label>
                                <p class="commande-nb-hint" id="materiel-aide">
                                    Service gratuit. Le matériel doit être restitué sous 10 jours ouvrés après la prestation, sinon 600 € de frais sont facturés (voir <a href="<?= $rootPath ?>pages/cgv.php">CGV</a>).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RÉCAPITULATIF : prix calculé par le serveur via fetch (api/prix-commande.php) -->
                <aside class="commande-sidebar" aria-label="Récapitulatif de la commande">
                    <div class="commande-recap">
                        <div class="commande-recap__header">
                            <h2 class="commande-recap__label">Récapitulatif</h2>
                        </div>
                        <div class="commande-recap__body" id="recap-body" aria-live="polite">
                            <div class="recap-ligne">
                                <span class="recap-ligne__lib">Menu sélectionné</span>
                                <span class="recap-ligne__val" id="recap-menu">-</span>
                            </div>
                            <div class="recap-ligne">
                                <span class="recap-ligne__lib">Nombre de personnes</span>
                                <span class="recap-ligne__val" id="recap-nb">-</span>
                            </div>
                            <div class="recap-ligne">
                                <span class="recap-ligne__lib">Prix du menu</span>
                                <span class="recap-ligne__val" id="recap-prix-menu">-</span>
                            </div>
                            <div class="recap-ligne" id="recap-remise-ligne" hidden>
                                <span class="recap-ligne__lib recap-ligne__lib--remise">Réduction 10 %</span>
                                <span class="recap-ligne__val recap-ligne__val--remise" id="recap-remise">-</span>
                            </div>
                            <div class="recap-ligne">
                                <span class="recap-ligne__lib">Frais de livraison</span>
                                <span class="recap-ligne__val" id="recap-livraison">-</span>
                            </div>
                            <div class="recap-total">
                                <span>Total</span>
                                <span id="recap-total">-</span>
                            </div>

                            <p class="auth-alert auth-alert--error mt-2" id="recap-erreur" role="alert" hidden></p>

                            <div class="recap-conditions" id="recap-conditions" hidden>
                                <strong>Conditions du menu :</strong>
                                <span id="recap-conditions-text"></span>
                            </div>
                        </div>
                        <div class="commande-recap__footer">
                            <button type="submit" class="auth-btn" id="btn-commander">Confirmer la commande
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                            <p class="recap-mention">En confirmant, vous acceptez nos <a href="<?= $rootPath ?>pages/cgv.php" class="auth-link">conditions générales de vente</a>.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</section>
