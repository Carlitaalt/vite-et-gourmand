<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="row gy-5">

        <!-- Colonne 1 : Marque -->
         <div class="col-12 col-md-3">
            <div class="footer-brand">
                <img src="<?= isset($rootPath) ? $rootPath : '../' ?>assets/images/image_logo_vert-removebg-preview.png" alt="Logo Vite & Gourmand" class="footer-logo">
                <p class="footer-tagline">Traiteur événementiel<br>Bordeaux · Depuis 1995</p>
                <p class="footer-desc">Julie et Rosé vous proposent des menus gastronomiques pour tous vos événements, élaborés avec passion et des produits frais.</p>
            </div>
         </div>
        
        <!-- Colonne 2 : Horaires -->
         <div class="col-12 col-md-auto">
            <h2 class="footer-title">
                <i class="bi bi-clock" aria-hidden="true"></i>
                Horaires d'ouverture
            </h2>
            <ul class="footer-horaires" aria-label="Horaires d'ouverture">
                <li>
                    <span class="footer-jour">Lundi</span>
                    <span class="footer-heure">09h00 - 18h00</span>
                </li>
                <li>
                    <span class="footer-jour">Mardi</span>
                    <span class="footer-heure">09h00 - 18h00</span>
                </li>
                <li>
                    <span class="footer-jour">Mercredi</span>
                    <span class="footer-heure">09h00 - 18h00</span>
                </li>
                <li>
                    <span class="footer-jour">Jeudi</span>
                    <span class="footer-heure">09h00 - 18h00</span>
                </li>
                <li>
                    <span class="footer-jour">Vendredi</span>
                    <span class="footer-heure">09h00 - 20h00</span>
                </li>
                <li>
                    <span class="footer-jour">Samedi</span>
                    <span class="footer-heure">10h00 - 20h00</span>
                </li>
                <li>
                    <span class="footer-jour">Dimanche</span>
                    <span class="footer-heure">10h00 - 16h00</span>
                </li>
            </ul>
         </div>

        <!-- Colonne 3 : Liens utiles -->
         <div class="col-12 col-md-auto">
            <h2 class="footer-title">
                <i class="bi bi-link-45deg" aria-hidden="true"></i>
                Liens utiles
            </h2>
            <ul class="footer-links" aria-label="Liens utiles">
                <li>
                    <a href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/accueil.php">
                        <i class="bi bi-house" aria-hidden="true"></i>
                        Accueil
                    </a>
                </li>
                <li>
                    <a href="<?=  isset($rootPath) ? $rootPath : '../' ?>pages/menus.php">
                        <i class="bi bi-journal-richtext" aria-hidden="true"></i>
                        Nos menus
                    </a>
                </li>
                <li>
                    <a href="<?=  isset($rootPath) ? $rootPath : '../' ?>pages/contact.php">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        Contact
                    </a>
                </li>
                <li>
                    <a href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/mentions-legales.php">
                        <i class="bi bi-file-text" aria-hidden="true"></i>
                        Mentions légales
                    </a>
                </li>
                <li>
                    <a href="<?=  isset($rootPath) ? $rootPath : '../' ?>pages/cgv.php">
                        <i class="bi bi-file-earmark-check" aria-hidden="true"></i>
                        Conditions générales de vente
                    </a>
                </li>
            </ul>
         </div>

        </div>

        <!-- Barre de copyright -->
         <div class="footer-bottom">
            <p class="footer-copy">
                &copy; <?= date('Y') ?> Vite &amp; Gourmand · Tous drois réservés ·
                <a href="<?=  isset($rootPath) ? $rootPath : '../' ?>pages/mentions-legales.php">Mentions légales</a>
                <a href="<?=  isset($rootPath) ? $rootPath : '../' ?>pages/cgv.php">CGV</a>
            </p>
         </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 
<!-- JS Global -->
<script src="<?= isset($rootPath) ? $rootPath : '../' ?>assets/js/main.js"></script>