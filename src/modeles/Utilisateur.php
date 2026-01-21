<?php
/**
 * Modèle Utilisateur
 * Gère les opérations CRUD sur les utilisateurs
 * BTS SIO SLAM
 */

namespace App\Modeles;

use App\Modeles\BaseDeDonnees;
use PDO;

class Utilisateur
{
    /**
     * Trouver un utilisateur par son email
     * @param string $email
     * @return array|false
     */
    public static function trouverParEmail(string $email)
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('SELECT * FROM utilisateur WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Trouver un utilisateur par son ID
     * @param int $id
     * @return array|false
     */
    public static function trouverParId(int $id)
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('SELECT * FROM utilisateur WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Vérifier les identifiants de connexion
     * @param string $email
     * @param string $motDePasse
     * @return array|false
     */
    public static function verifierConnexion(string $email, string $motDePasse)
    {
        $utilisateur = self::trouverParEmail($email);
        
        if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            // Retirer le mot de passe du résultat
            unset($utilisateur['mot_de_passe']);
            return $utilisateur;
        }
        
        return false;
    }

    /**
     * Lister tous les utilisateurs
     * @return array
     */
    public static function listerTous(): array
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query('SELECT id, nom, prenom, email, service, poste, date_embauche, role, created_at FROM utilisateur ORDER BY nom, prenom');
        return $stmt->fetchAll();
    }

    /**
     * Créer un nouvel utilisateur
     * @param array $donnees
     * @return int ID de l'utilisateur créé
     */
    public static function creer(array $donnees): int
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            INSERT INTO utilisateur (nom, prenom, email, service, poste, date_embauche, role, mot_de_passe)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $motDePasseHash = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'],
            $donnees['service'],
            $donnees['poste'],
            $donnees['date_embauche'],
            $donnees['role'],
            $motDePasseHash
        ]);
        
        return (int) $db->lastInsertId();
    }

    /**
     * Modifier un utilisateur
     * @param int $id
     * @param array $donnees
     * @return bool
     */
    public static function modifier(int $id, array $donnees): bool
    {
        $db = BaseDeDonnees::getInstance();
        
        // Si un nouveau mot de passe est fourni
        if (!empty($donnees['mot_de_passe'])) {
            $stmt = $db->prepare('
                UPDATE utilisateur 
                SET nom = ?, prenom = ?, email = ?, service = ?, poste = ?, 
                    date_embauche = ?, role = ?, mot_de_passe = ?
                WHERE id = ?
            ');
            $motDePasseHash = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);
            $stmt->execute([
                $donnees['nom'],
                $donnees['prenom'],
                $donnees['email'],
                $donnees['service'],
                $donnees['poste'],
                $donnees['date_embauche'],
                $donnees['role'],
                $motDePasseHash,
                $id
            ]);
        } else {
            // Sans changement de mot de passe
            $stmt = $db->prepare('
                UPDATE utilisateur 
                SET nom = ?, prenom = ?, email = ?, service = ?, poste = ?, 
                    date_embauche = ?, role = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $donnees['nom'],
                $donnees['prenom'],
                $donnees['email'],
                $donnees['service'],
                $donnees['poste'],
                $donnees['date_embauche'],
                $donnees['role'],
                $id
            ]);
        }
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Supprimer un utilisateur
     * @param int $id
     * @return bool
     */
    public static function supprimer(int $id): bool
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('DELETE FROM utilisateur WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
