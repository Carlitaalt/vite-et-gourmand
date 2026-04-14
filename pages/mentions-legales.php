<?php
session_start();
require_once '..includes/db.php';
require_once '../includes/auth.php';

$pageTitle = 'Mentions légales';
$rootPath = '../';
$currentPage = 'mentions-legales';

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<section class="section-presentation">
    <div class="container text-center">

        <span class="section-label">Informations légales</span>
        <h1 class="section-title">Mentions légales</h1>
        <div class="divider-or"></div>

        <div class="legal-wrapper">
            <div class="legal-card">

                <h3>Éditeur du site</h3>
                <p>
                    Vite & Gourmand – Micro-entreprise<br>
                    Responsable : Julie & José<br>
                    Email : contact@viteetgourmand.fr
                </p>

                <h3>Hébergement</h3>
                <p>
                    OVH / Hostinger<br>
                    Adresse : à compléter
                </p>

                <h3>Propriété intellectuelle</h3>
                <p>
                    Tous les contenus sont protégés. Toute reproduction est interdite.
                </p>

                <h3>Données personnelles</h3>
                <p>
                    Vos données sont utilisées uniquement pour répondre à vos demandes.
                </p>

                <h3>Responsabilité</h3>
                <p>
                    Vite & Gourmand ne peut être tenu responsable d’éventuelles erreurs.
                </p>

            </div>

        </div>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>