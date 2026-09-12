<?php

require_once __DIR__ . '/../src/bootstrap.php';

(new App\Controller\PageController())->afficher('mentions-legales', 'Mentions légales');
