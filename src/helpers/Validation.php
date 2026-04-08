<?php

namespace App\Helpers;

class Validation
{
    
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
     
    public static function date(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
     
    public static function dateFutur(string $date): bool
    {
        if (!self::date($date)) {
            return false;
        }
        return new \DateTime($date) >= new \DateTime('today');
    }
     
    public static function dateFinApresDebut(string $dateDebut, string $dateFin): bool
    {
        if (!self::date($dateDebut) || !self::date($dateFin)) {
            return false;
        }
        return new \DateTime($dateFin) >= new \DateTime($dateDebut);
    }
 
    public static function nettoyer(string $value): string
    {
        return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}
