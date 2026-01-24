<?php

namespace App\Helpers;

class Flash
{
    /**
     * Ajouter un message flash
     * @param string $type success|error|warning|info
     * @param string $message
     */
    public static function ajouter(string $type, string $message): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Récupérer tous les messages flash
     * @return array
     */
    public static function recuperer(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    /**
     * Méthodes de raccourci
     */
    public static function success(string $message): void
    {
        self::ajouter('success', $message);
    }

    public static function error(string $message): void
    {
        self::ajouter('error', $message);
    }

    public static function warning(string $message): void
    {
        self::ajouter('warning', $message);
    }

    public static function info(string $message): void
    {
        self::ajouter('info', $message);
    }
}
