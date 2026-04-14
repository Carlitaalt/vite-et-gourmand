<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';


$pageTitle = "Conditions Générales de Vente";
$rootPath = '../';
$currentPage = 'cgv';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<section class="section-presentation">
    <div class="container text-center">
        <span class="section-label">Informations clients</span>
        <h1 class="section-title">Conditions Générales de Vente</h1>
        <div class="divider-or"></div>

        <div class="legal-wrapper">
            <div class="legal-card">
            
                <h3>1. Objet</h3>
                <p>
                    Les présentes Conditions Générales de Vente (CGV) définissent les relations entre
                    <strong>Vite & Gourmand</strong> et ses clients dans le cadre de prestations de restauration événementielle.
                </p>

                <h3>2. Commande</h3>
                <p>
                    Toute commande est validée après acceptation du devis.
                    Vite & Gourmand se réserve le droit de refuser une commande en cas d'indisponibilité.
                </p>

                <h3>3. Tarifs</h3>
                <p>
                    Les prix sont exprimés en euros (€) et peuvent varier selon la prestation.
                    Un devis personnalisé est établi avant toute validation.
                </p>

                <h3>4. Paiement</h3>
                <p>
                    Un accompte peut être demandé à la commande.
                    Le solde est à régler avant ou le jour de la prestation.
                </p>

                <h3>5. Information du site</h3>
                <p>
                    Les informations présentes sur ce site sont données à titre indicatif et peuvent évoluer.
                </p>

                <h3>6. Annulation</h3>
                <p>
                    Toute annulation doit être effectuée par écrit.
                    Des frais peuvent être appliqués selon le délai.
                </p>

                <h3>7. Livraison</h3>
                <p>
                    Vite & Gourmand s'engage à fournir un service de qualité après la livraison des produits.
                </p>
            </div>
        </div>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>