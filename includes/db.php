<?php

//Fuseau horaire
date_default_timezone_set('Europe/Paris');

// CONNEXION PDO

$host = 'sql310.infinityfree.com';
$dbname = 'if0_41824942_vite_gourmand';
$user = 'if0_41824942';
$password = 'thoy8NILVbiQ';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Erreur SQL : ' . $e->getMessage());
}
