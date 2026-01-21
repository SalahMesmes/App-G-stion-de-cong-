<?php
/**
 * Helper Validation
 * Fonctions de validation des données
 * BTS SIO SLAM
 */

namespace App\Helpers;

class Validation
{
    /**
     * Valider un email
     * @param string $email
     * @return bool
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valider une date au format Y-m-d
     * @param string $date
     * @return bool
     */
    public static function date(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Vérifier qu'une date est dans le futur
     * @param string $date Format Y-m-d
     * @return bool
     */
    public static function dateFutur(string $date): bool
    {
        if (!self::date($date)) {
            return false;
        }
        return new \DateTime($date) >= new \DateTime('today');
    }

    /**
     * Vérifier qu'une date de fin est après la date de début
     * @param string $dateDebut
     * @param string $dateFin
     * @return bool
     */
    public static function dateFinApresDebut(string $dateDebut, string $dateFin): bool
    {
        if (!self::date($dateDebut) || !self::date($dateFin)) {
            return false;
        }
        return new \DateTime($dateFin) >= new \DateTime($dateDebut);
    }

    /**
     * Nettoyer une chaîne de caractères
     * @param string $value
     * @return string
     */
    public static function nettoyer(string $value): string
    {
        return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}
