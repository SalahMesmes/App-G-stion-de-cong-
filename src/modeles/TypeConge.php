<?php
/**
 * Modèle TypeConge
 * Gère les types de congés
 * BTS SIO SLAM
 */

namespace App\Modeles;

use App\Modeles\BaseDeDonnees;

class TypeConge
{
    /**
     * Trouver un type de congé par son ID
     * @param int $id
     * @return array|false
     */
    public static function trouverParId(int $id)
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('SELECT * FROM type_conge WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Lister tous les types de congés actifs
     * @return array
     */
    public static function listerActifs(): array
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query('SELECT * FROM type_conge WHERE actif = 1 ORDER BY libelle');
        return $stmt->fetchAll();
    }

    /**
     * Lister tous les types de congés (actifs et inactifs)
     * @return array
     */
    public static function listerTous(): array
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query('SELECT * FROM type_conge ORDER BY libelle');
        return $stmt->fetchAll();
    }

    /**
     * Créer un nouveau type de congé
     * @param array $donnees
     * @return int ID du type créé
     */
    public static function creer(array $donnees): int
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            INSERT INTO type_conge (code, libelle, justificatif_obligatoire, actif)
            VALUES (?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $donnees['code'],
            $donnees['libelle'],
            isset($donnees['justificatif_obligatoire']) ? 1 : 0,
            isset($donnees['actif']) ? 1 : 0
        ]);
        
        return (int) $db->lastInsertId();
    }

    /**
     * Modifier un type de congé
     * @param int $id
     * @param array $donnees
     * @return bool
     */
    public static function modifier(int $id, array $donnees): bool
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            UPDATE type_conge 
            SET code = ?, libelle = ?, justificatif_obligatoire = ?, actif = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            $donnees['code'],
            $donnees['libelle'],
            isset($donnees['justificatif_obligatoire']) ? 1 : 0,
            isset($donnees['actif']) ? 1 : 0,
            $id
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Supprimer un type de congé
     * @param int $id
     * @return bool
     */
    public static function supprimer(int $id): bool
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('DELETE FROM type_conge WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
