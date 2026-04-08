<?php

namespace App\Helpers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class TwigHelper
{
    private static ?Environment $instance = null;

     @return Environment
   
    public static function getInstance(): Environment
    {
        if (self::$instance === null) {
            $loader = new FilesystemLoader(VIEW_PATH);
            self::$instance = new Environment($loader, [
                'debug' => false,
                'cache' => false
            ]);

            // Ajouter la fonction flash_messages()
            $flashMessages = function() {
                return Flash::recuperer();
            };
            self::$instance->addFunction(new \Twig\TwigFunction('flash_messages', $flashMessages));

            // Ajouter un filtre pour formater les dates
            $dateFilter = new \Twig\TwigFilter('date', function($date, $format = 'd/m/Y') {
                if (empty($date)) return '';
                if (is_string($date)) {
                    try {
                        $date = new \DateTime($date);
                    } catch (\Exception $e) {
                        return '';
                    }
                }
                return $date->format($format);
            });
            self::$instance->addFilter($dateFilter);

            // Ajouter les constantes globales
            self::$instance->addGlobal('app_name', APP_NAME);
            self::$instance->addGlobal('app_url', APP_URL);
        }

        return self::$instance;
    }
}
