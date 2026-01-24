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
        
        // Convertir les valeurs booléennes en 0 ou 1
        $justificatif = (isset($donnees['justificatif_obligatoire']) && $donnees['justificatif_obligatoire'] === true) ? 1 : 0;
        $actif = (isset($donnees['actif']) && $donnees['actif'] === true) ? 1 : 0;
        
        $stmt->execute([
            $donnees['code'],
            $donnees['libelle'],
            $justificatif,
            $actif
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
        try {
            $db = BaseDeDonnees::getInstance();
            
            // Vérifier si le code existe déjà pour un autre type de congé
            $stmtCheck = $db->prepare('SELECT id FROM type_conge WHERE code = ? AND id != ?');
            $stmtCheck->execute([$donnees['code'], $id]);
            if ($stmtCheck->fetch()) {
                return false; // Code déjà utilisé par un autre type
            }
            
            $stmt = $db->prepare('
                UPDATE type_conge 
                SET code = ?, libelle = ?, justificatif_obligatoire = ?, actif = ?
                WHERE id = ?
            ');
            
            // Convertir les valeurs booléennes en 0 ou 1
            $justificatif = (isset($donnees['justificatif_obligatoire']) && $donnees['justificatif_obligatoire'] === true) ? 1 : 0;
            $actif = (isset($donnees['actif']) && $donnees['actif'] === true) ? 1 : 0;
            
            $stmt->execute([
                $donnees['code'],
                $donnees['libelle'],
                $justificatif,
                $actif,
                $id
            ]);
            
            // Retourner true si la requête a réussi (même si rowCount = 0 car valeurs identiques)
            return true;
        } catch (\PDOException $e) {
            error_log('Erreur lors de la modification du type de congé: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un type de congé est utilisé par des demandes
     * @param int $id
     * @return int Nombre de demandes utilisant ce type
     */
    public static function estUtilise(int $id): int
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) as nb FROM demande_conge WHERE type_conge_id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return (int) $result['nb'];
    }

    /**
     * Supprimer un type de congé
     * @param int $id
     * @return bool|string True si succès, message d'erreur sinon
     */
    public static function supprimer(int $id)
    {
        // Vérifier si le type est utilisé par des demandes
        $nbDemandes = self::estUtilise($id);
        if ($nbDemandes > 0) {
            return "Ce type de congé est utilisé par $nbDemandes demande(s). Vous ne pouvez pas le supprimer. Désactivez-le plutôt.";
        }

        try {
            $db = BaseDeDonnees::getInstance();
            $stmt = $db->prepare('DELETE FROM type_conge WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            return 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    }
}
