<?php
use App\Enum\Role;
use App\Security\Auth;

$currentPage ??= '';
$lienActif = fn(string $page): string => $currentPage === $page ? 'active" aria-current="page' : '';
?>
<nav class="navbar navbar-expand-lg navbar-vg" aria-label="Navigation principale">
    <div class="container">

        <!-- Logo / Marque -->
        <a class="navbar-brand" href="<?= $rootPath ?>pages/accueil.php">
            <img src="<?= $rootPath ?>assets/images/image_logo_vert-removebg-preview.png" alt="" class="navbar-logo">
            <span class="navbar-brand-text">Vite <span class="navbar-brand-amp">&</span> Gourmand</span>
        </a>

        <!-- Bouton burger mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Ouvrir le menu de navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                <li class="nav-item">
                    <a class="nav-link <?= $lienActif('accueil') ?>" href="<?= $rootPath ?>pages/accueil.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $lienActif('menus') ?>" href="<?= $rootPath ?>pages/menus.php">Nos menus</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $lienActif('contact') ?>" href="<?= $rootPath ?>pages/contact.php">Contact</a>
                </li>

                <li class="nav-item d-none d-lg-block">
                    <span class="nav-separator" aria-hidden="true"></span>
                </li>

                <?php if (Auth::estConnecte()): ?>
                    <!-- Utilisateur connecté -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle nav-user" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle" aria-hidden="true"></i>
                            <?= htmlspecialchars(Auth::prenom() ?? '') ?>
                            <span class="visually-hidden">(ouvrir mon espace)</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-vg">
                            <li>
                                <a href="<?= $rootPath ?>pages/mon-compte.php" class="dropdown-item">
                                    <i class="bi bi-person" aria-hidden="true"></i>
                                    Mon espace
                                </a>
                            </li>

                            <?php if (Auth::role() === Role::Administrateur): ?>
                                <li>
                                    <a href="<?= $rootPath ?>pages/espace-admin.php" class="dropdown-item">
                                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                        Administration
                                    </a>
                                </li>
                            <?php elseif (Auth::role() === Role::Employe): ?>
                                <li>
                                    <a href="<?= $rootPath ?>pages/espace-employe.php" class="dropdown-item">
                                        <i class="bi bi-briefcase" aria-hidden="true"></i>
                                        Espace employé
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="<?= $rootPath ?>actions/deconnexion.php" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                    Se déconnecter
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Visiteur non connecté -->
                    <li class="nav-item">
                        <a class="nav-link <?= $lienActif('connexion') ?>" href="<?= $rootPath ?>pages/connexion.php">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $rootPath ?>pages/inscription.php" class="btn btn-or ms-lg-2">S'inscrire</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>

    </div>
</nav>
