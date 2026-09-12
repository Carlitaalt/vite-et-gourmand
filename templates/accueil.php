<?php
/** @var App\Entity\Avis[] $avis */

$equipe = [
    ['nom' => 'Julie', 'role' => 'Cheffe', 'img' => 'team-1.jpg', 'bio' => 'Passionnée par les produits de saison, elle crée des menus qui racontent une histoire.'],
    ['nom' => 'José', 'role' => 'Logistique', 'img' => 'team-2.jpg', 'bio' => 'Expert en organisation, il veille à ce que chaque détail technique soit parfait.'],
    ['nom' => 'Romain', 'role' => 'Serveur', 'img' => 'team-3.jpg', 'bio' => "Son sens du service et son sourire sont les garants d'une ambiance réussie."],
];
?>

<section class="hero-section">
    <div class="hero-content-box">
        <span class="section-label">Restauration événementielle</span>

        <h1 class="hero-title">
            Une cuisine <br>
            <span class="hero-title-accent">généreuse</span>
        </h1>

        <p class="hero-desc">
            Vite & Gourmand sublime vos événements avec des menus soignés, des produits frais et une équipe passionnée. De l'apéritif au dessert, nous créons des moments inoubliables.
        </p>

        <div class="hero-actions">
            <a href="<?= $rootPath ?>pages/menus.php" class="btn btn-vg-primary">Découvrir nos menus</a>
            <a href="<?= $rootPath ?>pages/contact.php" class="btn btn-or">Nous contacter</a>
        </div>
    </div>
</section>

<!-- PRÉSENTATION -->
<section class="section-presentation">
    <div class="container">
        <div class="pres-inner">
            <div class="pres-col-img">
                <div class="pres-img-wrapper">
                    <img src="<?= $rootPath ?>assets/images/presentation.jpg" alt="Préparation artisanale d'un plat en cuisine">
                </div>
            </div>

            <div class="pres-col-text">
                <span class="section-label">Notre histoire</span>
                <h2 class="section-title">Une passion, <br>deux personnes</h2>
                <div class="divider-or"></div>

                <p class="pres-text">
                    Depuis 25 ans à Bordeaux, <strong>Vite & Gourmand</strong> est une entreprise de restauration événementielle à taille humaine.
                    Julie et José mettent tout leur savoir-faire au service de vos événements professionnels et privés.
                </p>

                <p class="pres-text">
                    De la conception du menu à la livraison, nous nous occupons de tout pour que vous puissiez profiter pleinement de vos moments importants.
                </p>
            </div>
        </div>
        <div class="pres-stats">
            <div class="pres-stat">
                <span class="pres-stat-number">25 ans</span>
                <span class="pres-stat-label">d'expérience à Bordeaux</span>
            </div>
            <div class="pres-stat">
                <span class="pres-stat-number">98%</span>
                <span class="pres-stat-label">Clients satisfaits</span>
            </div>
        </div>
    </div>
</section>

<!-- ÉQUIPE : mise en avant du professionnalisme -->
<section class="section-equipe">
    <div class="container text-center">
        <span class="section-label">Qui sommes-nous ?</span>
        <h2 class="section-title">Notre équipe</h2>
        <div class="divider-or mx-auto"></div>

        <div class="row justify-content-center mt-5 g-4">
            <?php foreach ($equipe as $membre): ?>
                <div class="col-md-5 col-lg-4">
                    <div class="card team-card">
                        <div class="team-img-wrapper">
                            <img src="<?= $rootPath ?>assets/images/<?= $membre['img'] ?>" alt="Portrait de <?= htmlspecialchars($membre['nom']) ?>" class="team-img">
                        </div>
                        <div class="team-info">
                            <h3 class="team-name"><?= htmlspecialchars($membre['nom']) ?></h3>
                            <span class="team-role"><?= htmlspecialchars($membre['role']) ?></span>
                            <p class="team-bio"><?= htmlspecialchars($membre['bio']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PROCESS -->
<section class="section-process">
    <div class="container text-center">
        <span class="section-label">Comment ça marche ?</span>
        <h2 class="section-title">Une organisation simple et efficace</h2>
        <div class="divider-or mx-auto"></div>

        <div class="row mt-5 g-4">
            <div class="process-card">
                <div class="process-icon"><i class="bi bi-journal-check" aria-hidden="true"></i></div>
                <h3>1. Choix du menu</h3>
                <p>Parcourez nos menus, filtrez selon votre budget, le thème ou le régime alimentaire, puis commandez en ligne.</p>
            </div>
            <div class="process-card">
                <div class="process-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></div>
                <h3>2. Validation</h3>
                <p>Notre équipe accepte votre commande et vous tient informé à chaque étape depuis votre espace.</p>
            </div>
            <div class="process-card">
                <div class="process-icon"><i class="bi bi-truck" aria-hidden="true"></i></div>
                <h3>3. Livraison</h3>
                <p>Nous livrons directement sur votre lieu d'événement, prêt à être dégusté.</p>
            </div>
        </div>
        <div class="process-cta">
            <a href="<?= $rootPath ?>pages/menus.php" class="btn btn-vg-primary">Voir les menus</a>
        </div>
    </div>
</section>

<!-- AVIS : uniquement les avis validés par un employé -->
<section class="section-avis">
    <div class="container text-center">
        <span class="section-label">Ce qu'ils disent</span>
        <h2 class="section-title-light">Avis de nos clients</h2>
        <div class="divider-or mx-auto"></div>

        <?php if (empty($avis)): ?>
            <p class="avis-texte">Aucun avis n'a encore été publié.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($avis as $unAvis): ?>
                    <div class="col-md-4">
                        <div class="avis-card">
                            <div class="avis-stars" role="img" aria-label="Note : <?= $unAvis->getNote() ?> sur 5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= $i <= $unAvis->getNote() ? 'bi-star-fill' : 'bi-star' ?>" aria-hidden="true"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="avis-texte">« <?= htmlspecialchars($unAvis->getCommentaire()) ?> »</p>
                            <div class="avis-auteur">
                                <div class="avis-avatar" aria-hidden="true">
                                    <?= htmlspecialchars(mb_strtoupper(mb_substr($unAvis->getAuteurPrenom() ?? 'C', 0, 1))) ?>
                                </div>
                                <div>
                                    <span class="avis-nom"><?= htmlspecialchars($unAvis->getAuteurAffiche()) ?></span>
                                    <span class="avis-date"><?= $unAvis->getCreeLe()?->format('d/m/Y') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="section-cta py-5">
    <div class="container">
        <div class="cta-box">
            <div class="cta-deco"></div>
            <h2 class="cta-title">Prêt à sublimer votre événement ?</h2>
            <p class="cta-desc">Découvrez dès maintenant nos menus ou contactez-nous pour une demande particulière.</p>
            <div class="cta-actions">
                <a href="<?= $rootPath ?>pages/menus.php" class="btn-vg-primary">Voir nos menus</a>
                <a href="<?= $rootPath ?>pages/contact.php" class="btn-or">Nous contacter</a>
            </div>
        </div>
    </div>
</section>
