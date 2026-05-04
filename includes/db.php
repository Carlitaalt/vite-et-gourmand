<?php

//Fuseau horaire
date_default_timezone_set('Europe/Paris');

// CONNEXION PDO

$host = 'sql310.infinityfree.com';
$dbname = 'if0_41824942_vite_gourmand';
$user = 'if0_41824942';
$password = 'thoy8NILVbiQ';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_PERSISTENT => false, //Évite les fuites de mémoires
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Erreur BDD : ' . $e->getMessage());
    die('Service indisponible. Veuillez réessayer plus tard.');
}
