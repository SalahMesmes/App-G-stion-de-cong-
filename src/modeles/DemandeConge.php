<?php

namespace App\Modeles;

use App\Modeles\BaseDeDonnees;
use DateTime;

class DemandeConge
{
    /**
     * Calculer le nombre de jours ouvrés entre deux dates (lundi-vendredi)
     * @param string $dateDebut Format Y-m-d
     * @param string $dateFin Format Y-m-d
     * @param string $demiJournee NONE|AM|PM
     * @return float
     */
    public static function calculerJoursOuvres(string $dateDebut, string $dateFin, string $demiJournee = 'NONE'): float
    {
        $debut = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        
        // Si demi-journée et même jour
        if ($demiJournee !== 'NONE' && $dateDebut === $dateFin) {
            return 0.5;
        }
        
        $joursOuvres = 0;
        $current = clone $debut;
        
        while ($current <= $fin) {
            $jourSemaine = (int) $current->format('w'); // 0=dimanche, 1=lundi, ..., 6=samedi
            // Lundi = 1, Vendredi = 5
            if ($jourSemaine >= 1 && $jourSemaine <= 5) {
                $joursOuvres++;
            }
            $current->modify('+1 day');
        }
        
        return (float) $joursOuvres;
    }

    /**
     * Vérifier s'il y a un chevauchement avec une autre demande
     * @param int $utilisateurId
     * @param string $dateDebut
     * @param string $dateFin
     * @param int|null $exclureId ID de la demande à exclure (pour modification)
     * @return bool
     */
    public static function verifierChevauchement(int $utilisateurId, string $dateDebut, string $dateFin, ?int $exclureId = null): bool
    {
        $db = BaseDeDonnees::getInstance();
        
        $sql = '
            SELECT COUNT(*) as nb
            FROM demande_conge
            WHERE utilisateur_id = ?
            AND statut IN (?, ?)
            AND (
                (date_debut <= ? AND date_fin >= ?)
                OR (date_debut <= ? AND date_fin >= ?)
            )
        ';
        
        $params = [
            $utilisateurId,
            STATUT_EN_ATTENTE,
            STATUT_ACCEPTE,
            $dateDebut,
            $dateDebut,
            $dateFin,
            $dateFin
        ];
        
        if ($exclureId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $exclureId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return (int) $result['nb'] > 0;
    }

    /**
     * Trouver une demande par son ID
     * @param int $id
     * @return array|false
     */
    public static function trouverParId(int $id)
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            SELECT dc.*, 
                   u.nom as utilisateur_nom, u.prenom as utilisateur_prenom,
                   tc.libelle as type_conge_libelle, tc.justificatif_obligatoire,
                   d.nom as decision_nom, d.prenom as decision_prenom
            FROM demande_conge dc
            LEFT JOIN utilisateur u ON dc.utilisateur_id = u.id
            LEFT JOIN type_conge tc ON dc.type_conge_id = tc.id
            LEFT JOIN utilisateur d ON dc.decision_par = d.id
            WHERE dc.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Lister les demandes d'un utilisateur
     * @param int $utilisateurId
     * @return array
     */
    public static function listerParUtilisateur(int $utilisateurId): array
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            SELECT dc.*, tc.libelle as type_conge_libelle
            FROM demande_conge dc
            LEFT JOIN type_conge tc ON dc.type_conge_id = tc.id
            WHERE dc.utilisateur_id = ?
            ORDER BY dc.created_at DESC
        ');
        $stmt->execute([$utilisateurId]);
        return $stmt->fetchAll();
    }

    /**
     * Lister toutes les demandes en attente (pour manager)
     * @return array
     */
    public static function listerEnAttente(): array
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            SELECT dc.*, 
                   u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.service,
                   tc.libelle as type_conge_libelle
            FROM demande_conge dc
            LEFT JOIN utilisateur u ON dc.utilisateur_id = u.id
            LEFT JOIN type_conge tc ON dc.type_conge_id = tc.id
            WHERE dc.statut = ?
            ORDER BY dc.created_at ASC
        ');
        $stmt->execute([STATUT_EN_ATTENTE]);
        return $stmt->fetchAll();
    }

    /**
     * Créer une nouvelle demande
     * @param array $donnees
     * @return int ID de la demande créée
     */
    public static function creer(array $donnees): int
    {
        $db = BaseDeDonnees::getInstance();
        
        // Calculer les jours ouvrés
        $joursOuvres = self::calculerJoursOuvres(
            $donnees['date_debut'],
            $donnees['date_fin'],
            $donnees['demi_journee'] ?? 'NONE'
        );
        
        $stmt = $db->prepare('
            INSERT INTO demande_conge 
            (utilisateur_id, type_conge_id, date_debut, date_fin, demi_journee, 
             motif, commentaire, justificatif, jours_ouvres)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $donnees['utilisateur_id'],
            $donnees['type_conge_id'],
            $donnees['date_debut'],
            $donnees['date_fin'],
            $donnees['demi_journee'] ?? 'NONE',
            $donnees['motif'] ?? null,
            $donnees['commentaire'] ?? null,
            $donnees['justificatif'] ?? null,
            $joursOuvres
        ]);
        
        return (int) $db->lastInsertId();
    }

    /**
     * Accepter une demande
     * @param int $id
     * @param int $managerId
     * @param string|null $commentaire
     * @return bool
     */
    public static function accepter(int $id, int $managerId, ?string $commentaire = null): bool
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            UPDATE demande_conge 
            SET statut = ?, decision_par = ?, decision_date = NOW(), commentaire_decision = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            STATUT_ACCEPTE,
            $managerId,
            $commentaire,
            $id
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Refuser une demande
     * @param int $id
     * @param int $managerId
     * @param string|null $commentaire
     * @return bool
     */
    public static function refuser(int $id, int $managerId, ?string $commentaire = null): bool
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            UPDATE demande_conge 
            SET statut = ?, decision_par = ?, decision_date = NOW(), commentaire_decision = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            STATUT_REFUSE,
            $managerId,
            $commentaire,
            $id
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Annuler une demande
     * @param int $id
     * @return bool
     */
    public static function annuler(int $id): bool
    {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->prepare('
            UPDATE demande_conge 
            SET statut = ?
            WHERE id = ? AND statut = ?
        ');
        
        $stmt->execute([
            STATUT_ANNULE,
            $id,
            STATUT_EN_ATTENTE
        ]);
        
        return $stmt->rowCount() > 0;
    }
}
