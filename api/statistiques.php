<?php

require_once __DIR__ . '/../src/bootstrap.php';

(new App\Controller\Api\StatistiqueApiController())->index();
