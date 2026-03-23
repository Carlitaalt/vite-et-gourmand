<nav class="navbar navbar-expand-lg navbar-vg" aria-label="Naviguation principale">
    <div class="container">
        
    <!--Logo / Marque -->
    <a  class="navbar-brand" href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/accueil.php">
        <img src="<?= isset($rootPath) ? $rootPath : '../' ?>assets/images/image_logo_vert-removebg-preview.png" alt="Logo Vite & Gourmand" class="navbar-logo">
        <span class="navbar-brand-text">Vite <span class="navbar-brand-amp">&</span> Gourmand</span>
    </a>

    <!-- Bouton burger mobile -->
     <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Ouvrir le menu de naviguation">
        <span class="navbar-toggler-icon"></span>
     </button>

     <!-- Lien de naviguation -->
      <div class="collapse navbar-collapse" id="navbarMenu">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

        <!-- Accueil -->
         <li class="nav-item">
            <a class="nav-link <?= (isset($currentPage) && $currentPage === 'accueil') ? 'active' : '' ?>" href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/accueil.php" <?= (isset($currentPage) && $currentPage === 'accueil') ? 'aria-current="page"' : '' ?>>Accueil</a>
         </li>

        <!-- Nos menus -->
         <li class="nav-item">
            <a class="nav-link <?= (isset($currentPage) && $currentPage === 'menus') ? 'active' : '' ?>" href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/menus.php" <?= (isset($currentPage) && $currentPage === 'menus') ? 'aria-current="page"' : '' ?>>Nos menus</a>
         </li>

        <!-- Contact -->
         <li class="nav-item">
            <a class="nav-link <?= (isset($currentPage) && $currentPage === 'contact') ? 'active' : '' ?>" href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/contact.php" <?= (isset($currentPage) && $currentPage === 'contact') ? 'aria-current="page"' : '' ?>>Contact</a>
         </li>

        <!-- Séparateur visuel -->
         <li class="nav-item d-none d-lg-block">
            <span class="nav-separator" aria-hidden="true"></span>
         </li>

        <!-- Connexion / Espace utilisateur -->
         <?php if (isset($_SESSION['utilisateur'])): ?>
            <!-- Utilisateur connecté -->
             <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle nav-user" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mon espace">
                    <i class="bi bi-person-circle" aria-hidden="true"></i>
                    <?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-vg">
                    <li>
                        <a href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/espace-utilisateur.php" class="dropdown-item">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            Mon espace
                        </a>
                    </li>
                    <?php if ($_SESSION['utilisateur']['role'] === 'employe' || $_SESSION['utilisateur']['role'] === 'administrateur'): ?>
                        <li>
                            <a href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/espace-employe.php" class="dropdown-item">
                                <i class="bi bi-briefcase" aria-hidden="true"></i>
                                Espace employé
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($_SESSION['utilisateur']['role'] === 'administrateur'): ?>
                        <li>
                            <a href="<?=  isset($rootPath) ? $rootPath : '../' ?>pages/espace-admin.php" class="dropdown-item">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                Administration
                            </a>
                        </li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/deconnexion.php" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            Se déconnecter
                        </a>
                    </li>
                </ul>
             </li>

            <?php else: ?>
                <!-- Visiteur non connecté -->
                 <li class="nav-item">
                    <a class="nav-link <?= (isset($currentPage) && $currentPage === 'connexion') ? 'active' : '' ?>" href="<?= isset($rootPath) ? $rootPath : '../' ?>pages/connexion.php">
                        <i class="bi bi-person" aria-hidden="true"></i>
                        Connexion
                    </a>
                 </li>
                 <li class="nav-item">
                    <a href="<?=  isset($rootPath) ? $rootPath : '../' ?>pages/inscription.php" class="btn btn-or ms-lg-2">
                        S'inscrire
                    </a>
                 </li>
            <?php endif; ?>

        </ul>
      </div>

    </div>
</nav>