<?php

namespace App\Middleware;

use App\Helpers\Flash;

class Authentification
{
    
    public static function verifierConnexion(): void
    {
        if (!isset($_SESSION['utilisateur'])) {
            Flash::warning('Vous devez être connecté pour accéder à cette page.');
            header('Location: index.php?route=auth/connexion');
            exit;
        }
    }

    public static function verifierManager(): void
    {
        self::verifierConnexion();
        
        if ($_SESSION['utilisateur']['role'] !== ROLE_MANAGER) {
            Flash::error('Accès réservé aux managers.');
            header('Location: index.php?route=tableau-de-bord');
            exit;
        }
    }
     
    public static function getUtilisateur(): ?array
    {
        return $_SESSION['utilisateur'] ?? null;
    }
     
     
    public static function estConnecte(): bool
    {
        return isset($_SESSION['utilisateur']);
    }
}
