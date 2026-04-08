<?php


namespace App\Controleurs;

use App\Middleware\Authentification;
use App\Helpers\TwigHelper;
use App\Modeles\DemandeConge;

class ControleurTableauDeBord
{
    
    public function index(): void
    {
        Authentification::verifierConnexion();
        
        $utilisateur = Authentification::getUtilisateur();
        
        $demandes = DemandeConge::listerParUtilisateur($utilisateur['id']);
        $demandesRecentes = array_slice($demandes, 0, 5); 
        
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
