
<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

$pageTitle = 'Accueil';
$rootPath = '../';
$currentPage = 'accueil';


require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<section class="hero-section">

    <div class="hero-content-box">
        <span class="section-label">Restauration événementielles</span>

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
                    <img src="<?= $rootPath ?>assets/images/presentation.jpg" alt="Cuisine artisanale">
                </div>
            </div>

            <div class="pres-col-text">
                <span class="section-label">Notre histoire</span>
                <h2 class="section-title">Une passion, <br>Deux personnes</h2>
                <div class="divider-or"></div>

                <p class="pres-text">
                    Fondée avec passion, <strong>Vite & Gourmand</strong> est une entreprise de restauration événementielle à taille humaine.
                    Nous mettons tout notre savoir-faire au service de vos événements professionnels et privés.
                </p>

                <p class="pres-text">
                    De la conception du menu à la livraison, nous nous occupons de tout pour que vous puissiez profiter pleinement de vos moments importants.
                </p>
            </div>
        </div>
        <div class="pres-stats">
            <div class="pres-stat">
                <span class="pres-stat-number">50+</span>
                <span class="pres-stat-label">Événements réalisés</span>
            </div>
            <div class="pres-stat">
                <span class="pres-stat-number">98%</span>
                <span class="pres-stat-label">Clients satisfaits</span>
            </div>
        </div>
    </div>
</section>



<!-- ÉQUIPE -->

<section class="section-equipe">
    <div class="container text-center">


        <span class="section-label">Qui sommes-nous ?</span>
        <h2 class="section-title">Notre équipe</h2>
        <div class="divider-or mx-auto"></div>

        <div class="row justify-content-center mt-5 g-4">

        <?php

        $team = [
            ["nom"=>"Julie", "role"=>"Cheffe", "img"=>"team-1.jpg", "bio" => "Passionnée par les produits de saison, elle crée des menus qui racontent une histoire."],
            ["nom"=>"José", "role"=>"Logistique", "img"=>"team-2.jpg", "bio" => "Expert en organisation, il veille à ce que chaque détail technique soit parfait."],
            ["nom"=>"Romain", "role"=>"Serveur", "img"=>"team-3.jpg", "bio" => "Son sens du service et son sourire sont les garants d'une ambiance réussie."]
        ];
        ?>

        <?php foreach ($team as $member): ?>
            <div class="col-md-5 col-lg-4">
                <div class="card team-card">
                    <div class="team-img-wrapper">
                        <img src="<?= $rootPath ?>assets/images/<?= $member['img'] ?>" alt="Photo de <?= htmlspecialchars($member['nom']) ?>" class="team-img">
                    </div>
                    <div class="team-info">
                        <h3 class="team-name"><?= htmlspecialchars($member['nom']) ?></h3>
                        <span class="team-role"><?= htmlspecialchars($member['role']) ?></span>
                        <p class="team-bio">
                            <?= htmlspecialchars($member['bio']) ?>
                        </p>
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
                <div class="process-icon">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <h3>1. Demande</h3>
                <p>Contactez-nous avec vos besoins : type d’événement, nombre de personnes et préférences culinaires.</p>
            </div>
            <div class="process-card">
                <div class="process-icon">
                    <i class="bi bi-journal-check"></i>
                </div>
                <h3>2. Devis & validation</h3>
                <p>Nous vous proposons un menu personnalisé. Une fois validé, nous lançons la préparation.</p>
            </div>
            <div class="process-card">
                <div class="process-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h3>3. Livraison</h3>
                <p>Nous livrons directement sur votre lieu d’événement, prêt à être dégusté.</p>
            </div>
        </div>
        <div class="process-cta">
            <a href="<?= $rootPath ?>pages/contact.php" class="btn btn-vg-primary">
                Faire une demande
            </a>
        </div>

    </div>
</section>

<!-- AVIS -->

<section class="section-avis">
    <div class="container text-center">

        <span class="section-label">Ce qu'ils disent</span>
        <h2 class="section-title-light">Avis de nos clients</h2>
        <div class="divider-or mx-auto"></div>

        <?php
        //Récuparation des avis validés depuis la base de données
        $avis = [];
        if(isset($pdo)){
            try {
                $stmt = $pdo->query("SELECT a.note, a.description as commentaire, a.created_at as date, u.prenom, u.nom FROM avis a JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id WHERE a.statut_avis_id = 2 ORDER BY a.created_at DESC LIMIT 6");
                $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                //Silencieux en production
            }
        }

        //Avis de démo si aucun avis

        if(empty($avis)){
            $avis = [
                ['prenom' => 'Sophie', 'nom' => 'M.', 'note' => 5, 'commentaire' => 'Une prestation exceptionnelle pour notre mariage ! Les menus étaient délicieux et le service impeccable. Toute notre famille a été ravie.', 'created_at'=> '2025-10-15'],
                ['prenom' => 'Thomas', 'nom' => 'B.', 'note' => 5, 'commentaire' => 'Nous avons fait appel à Vite & Gourmand pour notre séminaire d\'entreprise. Résultat parfait, livraison à l\'heure, qualité irréprochable.', 'created_at' => '2025-11-02'],
                ['prenom' => 'Marie', 'nom' => 'L.', 'note' => 4, 'commentaire' => 'Super expérience ! Les plats sont faits maison et ça se sent vraiment. Je recommande vivement pour tout type d\'événement.', 'created_at' => '2025-12-01']
            ];
        }
        ?>

        <div class="row g-4">
            <?php foreach ($avis as $avisItem): ?>
                <div class="col-md-4">
                    <div class="avis-card">
                        <div class="avis-stars">
                            <?php for ($i = 1; $i <=5; $i++): ?>
                                <i class="bi <?= $i <= $avisItem['note'] ? 'bi-star-fill' : 'bi-star' ?>" aria-hidden="true"></i>
                                <?php endfor; ?>
                        </div>
                        <p class="avis-texte">"<?= htmlspecialchars($avisItem['commentaire']) ?>"</p>
                        <div class="avis-auteur">
                            <div class="avis-avatar">
                                <?= strtoupper(substr($avisItem['prenom'], 0, 1)) ?>
                            </div>
                            <div>
                        <span class="avis-nom"><?= htmlspecialchars($avisItem['prenom']) . ' ' . htmlspecialchars($avisItem['nom']) ?></span>
                        <span class="avis-date"><?= date('d/m/Y', strtotime($avisItem['date'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->

<section class="section-cta py-5">
    <div class="container">
        <div class="cta-box">
            <div class="cta-deco"></div>
            <h2 class="cta-title">Prêt à sublimer votre événement ?</h2>
            <p class="cta-desc">Contactez-nous pour obtenir un devis personnalisé ou découvrez dès maintenant nos menus.</p>
            <div class="cta-actions">
                <a href="<?= $rootPath ?>pages/menus.php" class="btn-vg-primary">Voir nos menus</a>
                <a href="<?= $rootPath ?>pages/contact.php" class="btn-or">Demander un devis</a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>