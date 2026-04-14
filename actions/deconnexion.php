<?php
//Détruit la session et redirige vers la page d'accueil

session_start();
require_once '../includes/auth.php';

deconnecter();

header('Location: ../pages/accueil.php');
exit;