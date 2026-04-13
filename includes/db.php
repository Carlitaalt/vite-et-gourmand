<?php

// CONNEXION PDO

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'vite_gourmand';
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_PERSISTENT => false, //Évite les fuites de mémoires
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    error_log('Erreur BDD : ' . $e->getMessage());
    die('Service indisponible. Veuillez réessayer plus tard.');
}
