<?php
/**
 * Middleware d'authentification
 * Vérifie que l'utilisateur est connecté et a les droits nécessaires
 * BTS SIO SLAM
 */

namespace App\Middleware;

use App\Helpers\Flash;

class Authentification
{
    /**
     * Vérifier que l'utilisateur est connecté
     * Redirige vers la page de connexion si non connecté
     */
    public static function verifierConnexion(): void
    {
        if (!isset($_SESSION['utilisateur'])) {
            Flash::warning('Vous devez être connecté pour accéder à cette page.');
            header('Location: index.php?route=auth/connexion');
            exit;
        }
    }

    /**
     * Vérifier que l'utilisateur a le rôle MANAGER
     * Redirige vers le tableau de bord si pas manager
     */
    public static function verifierManager(): void
    {
        self::verifierConnexion();
        
        if ($_SESSION['utilisateur']['role'] !== ROLE_MANAGER) {
            Flash::error('Accès réservé aux managers.');
            header('Location: index.php?route=tableau-de-bord');
            exit;
        }
    }

    /**
     * Récupérer l'utilisateur connecté
     * @return array|null
     */
    public static function getUtilisateur(): ?array
    {
        return $_SESSION['utilisateur'] ?? null;
    }

    /**
     * Vérifier si l'utilisateur est connecté
     * @return bool
     */
    public static function estConnecte(): bool
    {
        return isset($_SESSION['utilisateur']);
    }
}
