<?php

namespace App\Controleurs;

use App\Modeles\Utilisateur;
use App\Helpers\Flash;
use App\Helpers\Validation;
use App\Helpers\TwigHelper;
use App\Middleware\Authentification;

class ControleurAuth
{
    //Afficher le formulaire de connexion
     
    public function connexion(): void
    {
        // Si déjà connecté rediriger vers le tableau de bord
        if (Authentification::estConnecte()) {
            header('Location: index.php?route=tableau-de-bord');
            exit;
        }

        $erreur = null;

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            if (empty($email) || empty($motDePasse)) {
                $erreur = 'Veuillez remplir tous les champs.';
            } elseif (!Validation::email($email)) {
                $erreur = 'Email invalide.';
            } else {
                $utilisateur = Utilisateur::verifierConnexion($email, $motDePasse);
                
                if ($utilisateur) {
                    // Connexion réussie
                    $_SESSION['utilisateur'] = $utilisateur;
                    Flash::success('Connexion réussie. Bienvenue ' . $utilisateur['prenom'] . ' !');
                    header('Location: index.php?route=tableau-de-bord');
                    exit;
                } else {
                    $erreur = 'Email ou mot de passe incorrect.';
                }
            }
        }

        // Afficher la vue
        $twig = TwigHelper::getInstance();
        echo $twig->render('auth/connexion.twig', [
            'erreur' => $erreur
        ]);
    }

    //Déconnexion
     
    public function deconnexion(): void
    {
        session_destroy();
        Flash::info('Vous avez été déconnecté.');
        header('Location: index.php?route=auth/connexion');
        exit;
    }
}
