<?php

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// CONNEXION PDO — les identifiants viennent des variables d'environnement

$host     = getenv('DB_HOST') ?: 'mysql';
$dbname   = getenv('DB_NAME');
$user     = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('[DB] ' . $e->getMessage());
    die('Erreur de connexion à la base de données.');
}