<?php
/**
 * En-tête et compteurs communs aux espaces employé et administrateur.
 *
 * @var App\Entity\Utilisateur $profil
 * @var string|null $badgeProfil
 * @var int $nbEnAttente
 * @var int $nbEnCours
 * @var int $nbAvisAttente
 * @var array|null $chiffreAffaires  (administrateur uniquement)
 * @var App\Entity\Employe[]|null $employes  (administrateur uniquement)
 */
?>
<div class="compte-header">
    <div class="compte-avatar employe-avatar" aria-hidden="true"><?= htmlspecialchars($profil->getInitiales()) ?></div>
    <div class="compte-header__info">
        <h1 class="compte-header__nom">
            <?= htmlspecialchars($profil->getNomComplet()) ?>
            <span class="employe-role-badge"><?= htmlspecialchars($badgeProfil ?? '') ?></span>
        </h1>
        <p class="compte-header__email"><?= htmlspecialchars($profil->getEmail()) ?></p>
    </div>
</div>

<div class="employe-stats">
    <div class="employe-stat-card employe-stat-card--attente">
        <div class="employe-stat__val"><?= $nbEnAttente ?></div>
        <div class="employe-stat__label">Commandes en attente</div>
    </div>
    <div class="employe-stat-card employe-stat-card--cours">
        <div class="employe-stat__val"><?= $nbEnCours ?></div>
        <div class="employe-stat__label">Commandes en cours</div>
    </div>
    <div class="employe-stat-card employe-stat-card--avis">
        <div class="employe-stat__val" data-compteur="avis-attente"><?= $nbAvisAttente ?></div>
        <div class="employe-stat__label">Avis à valider</div>
    </div>
    <?php if (isset($chiffreAffaires)): ?>
        <div class="employe-stat-card employe-stat-card--ca">
            <div class="employe-stat__val"><?= number_format($chiffreAffaires['total'], 0, ',', ' ') ?> €</div>
            <div class="employe-stat__label">Chiffre d'affaires</div>
        </div>
    <?php endif; ?>
    <?php if (isset($employes)): ?>
        <div class="employe-stat-card employe-stat-card--employes">
            <div class="employe-stat__val"><?= count(array_filter($employes, fn($e) => $e->getUtilisateur()->estActif())) ?></div>
            <div class="employe-stat__label">Employés actifs</div>
        </div>
    <?php endif; ?>
</div>
