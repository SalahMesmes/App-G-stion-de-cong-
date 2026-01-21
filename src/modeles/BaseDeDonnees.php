<?php
/**
 * Classe de connexion à la base de données
 * Utilise PDO avec requêtes préparées pour la sécurité
 * BTS SIO SLAM
 */

namespace App\Modeles;

use PDO;
use PDOException;

class BaseDeDonnees
{
    private static ?PDO $instance = null;

    /**
     * Connexion singleton à la base de données
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME
                );
                
                self::$instance = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }
        
        return self::$instance;
    }

    /**
     * Empêcher la duplication de l'instance
     */
    private function __construct() {}
    private function __clone() {}
}
