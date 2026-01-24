<?php


namespace App\Controleurs;

use App\Middleware\Authentification;
use App\Helpers\TwigHelper;
use App\Modeles\DemandeConge;

class ControleurTableauDeBord
{
    /**
     * Afficher le tableau de bord
     */
    public function index(): void
    {
        Authentification::verifierConnexion();
        
        $utilisateur = Authentification::getUtilisateur();
        
        // Récupérer les dernières demandes de l'utilisateur
        $demandes = DemandeConge::listerParUtilisateur($utilisateur['id']);
        $demandesRecentes = array_slice($demandes, 0, 5); // 5 dernières
        
        // Statistiques
        $stats = [
            'total' => count($demandes),
            'en_attente' => count(array_filter($demandes, fn($d) => $d['statut'] === STATUT_EN_ATTENTE)),
            'acceptees' => count(array_filter($demandes, fn($d) => $d['statut'] === STATUT_ACCEPTE)),
            'refusees' => count(array_filter($demandes, fn($d) => $d['statut'] === STATUT_REFUSE))
        ];

        $twig = TwigHelper::getInstance();
        echo $twig->render('tableau_de_bord/index.twig', [
            'utilisateur' => $utilisateur,
            'demandesRecentes' => $demandesRecentes,
            'stats' => $stats
        ]);
    }
}
