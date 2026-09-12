<?php
use App\Security\Csrf;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vite & Gourmand - Traiteur événementiel à Bordeaux depuis 25 ans. Menus pour tous vos événements.">
    <!-- Jeton CSRF lu par le JavaScript pour les appels fetch qui modifient des données -->
    <meta name="csrf-token" content="<?= Csrf::jeton() ?>">

    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Vite & Gourmand' : 'Vite & Gourmand - Traiteur à Bordeaux' ?></title>

    <link rel="icon" type="image/png" href="<?= $rootPath ?>assets/images/image_logo_vert-removebg-preview.png">

    <!-- Bootstrap, icônes et polices -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- CSS global -->
    <link href="<?= $rootPath ?>assets/css/variables.css" rel="stylesheet">
    <link href="<?= $rootPath ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= $rootPath ?>assets/css/complements.css" rel="stylesheet">
</head>
<body data-racine="<?= $rootPath ?>">
    <!-- Lien d'évitement (RGAA) : accès direct au contenu au clavier ou avec un lecteur d'écran -->
    <a href="#contenu" class="visually-hidden-focusable">Aller au contenu principal</a>

    <!-- Zone lue par les lecteurs d'écran après une action faite sans rechargement (fetch) -->
    <div id="annonces" class="visually-hidden" aria-live="polite"></div>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main id="contenu">
        <?php require __DIR__ . '/alertes.php'; ?>
