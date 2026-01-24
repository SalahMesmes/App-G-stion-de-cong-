<?php

// Configuration de la base de données (MAMP)
define('DB_HOST', 'localhost');
define('DB_PORT', '8889'); // Port MySQL par défaut de MAMP
define('DB_NAME', 'gestion_conges');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Mot de passe par défaut MAMP

// Configuration de l'application
define('APP_NAME', 'Gestion des Congés');
define('APP_URL', 'http://localhost:8888/App-Géstion%20de%20congé/public');

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
