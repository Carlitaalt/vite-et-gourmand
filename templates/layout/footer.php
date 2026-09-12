    </main>

    <footer class="footer">
        <div class="container">
            <div class="row gy-5">

                <!-- Colonne 1 : Marque -->
                <div class="col-12 col-md-3">
                    <div class="footer-brand">
                        <img src="<?= $rootPath ?>assets/images/image_logo_vert-removebg-preview.png" alt="Vite & Gourmand" class="footer-logo">
                        <p class="footer-tagline">Traiteur événementiel<br>Bordeaux · Depuis 25 ans</p>
                        <p class="footer-desc">Julie et José vous proposent des menus gastronomiques pour tous vos événements, élaborés avec passion et des produits frais.</p>
                    </div>
                </div>

                <!-- Colonne 2 : Horaires (du lundi au dimanche, modifiables depuis l'espace employé) -->
                <div class="col-12 col-md-auto">
                    <h2 class="footer-title">
                        <i class="bi bi-clock" aria-hidden="true"></i>
                        Horaires d'ouverture
                    </h2>
                    <ul class="footer-horaires">
                        <?php foreach ($horairesFooter as $horaire): ?>
                            <li>
                                <span class="footer-jour"><?= htmlspecialchars($horaire->getJour()) ?></span>
                                <span class="footer-heure <?= $horaire->estOuvert() ? '' : 'text-danger' ?>">
                                    <?= $horaire->getPlageAffichee() ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Colonne 3 : Liens utiles -->
                <div class="col-12 col-md-auto">
                    <h2 class="footer-title">
                        <i class="bi bi-link-45deg" aria-hidden="true"></i>
                        Liens utiles
                    </h2>
                    <ul class="footer-links">
                        <li><a href="<?= $rootPath ?>pages/accueil.php"><i class="bi bi-house" aria-hidden="true"></i> Accueil</a></li>
                        <li><a href="<?= $rootPath ?>pages/menus.php"><i class="bi bi-journal-richtext" aria-hidden="true"></i> Nos menus</a></li>
                        <li><a href="<?= $rootPath ?>pages/contact.php"><i class="bi bi-envelope" aria-hidden="true"></i> Contact</a></li>
                        <li><a href="<?= $rootPath ?>pages/mentions-legales.php"><i class="bi bi-file-text" aria-hidden="true"></i> Mentions légales</a></li>
                        <li><a href="<?= $rootPath ?>pages/cgv.php"><i class="bi bi-file-earmark-check" aria-hidden="true"></i> Conditions générales de vente</a></li>
                    </ul>
                </div>

            </div>

            <div class="footer-bottom">
                <p class="footer-copy">&copy; <?= date('Y') ?> Vite &amp; Gourmand · Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS global (fonctions communes, dont l'appel à l'API en fetch) puis JS propre à la page -->
    <script src="<?= $rootPath ?>assets/js/main.js"></script>
    <?php foreach ($scripts ?? [] as $script): ?>
        <script src="<?= str_starts_with($script, 'https://') ? $script : $rootPath . 'assets/js/' . $script ?>"></script>
    <?php endforeach; ?>
</body>
</html>
