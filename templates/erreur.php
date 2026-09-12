<?php /** @var string $message */ ?>
<section class="section-presentation">
    <div class="container text-center">
        <h1 class="section-title"><?= htmlspecialchars($pageTitle ?? 'Page introuvable') ?></h1>
        <div class="divider-or mx-auto"></div>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="<?= $rootPath ?>pages/menus.php" class="btn btn-vg-primary mt-3">Voir nos menus</a>
    </div>
</section>
