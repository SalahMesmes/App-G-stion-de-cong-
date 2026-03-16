<?php

// Configuration de la base de données distante (hébergement)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'salahmestaoui_db2');
define('DB_USER', 'salahmestaoui_db2');
define('DB_PASS', 'Salahmesmes123@');

// Configuration de l'application
define('APP_NAME', 'Gestion des Congés');
// URL de base détectée automatiquement (fonctionne en local et en production)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$scriptPath = str_replace('\\', '/', $scriptPath);
define('APP_URL', $protocol . '://' . $host . rtrim($scriptPath, '/'));

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('VIEW_PATH', SRC_PATH . '/vues');

// Configuration des sessions
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Rôles
define('ROLE_EMPLOYE', 'EMPLOYE');
define('ROLE_MANAGER', 'MANAGER');

// Statuts des demandes
define('STATUT_EN_ATTENTE', 'EN_ATTENTE');
define('STATUT_ACCEPTE', 'ACCEPTE');
define('STATUT_REFUSE', 'REFUSE');
define('STATUT_ANNULE', 'ANNULE');

// Demi-journées
define('DEMI_JOURNEE_NONE', 'NONE');
define('DEMI_JOURNEE_AM', 'AM');
define('DEMI_JOURNEE_PM', 'PM');
