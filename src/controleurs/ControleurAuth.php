<?php

namespace App\Controleurs;

use App\Modeles\Utilisateur;
use App\Helpers\Flash;
use App\Helpers\Validation;
use App\Helpers\TwigHelper;
use App\Middleware\Authentification;

class ControleurAuth
{
    public function connexion(): void
    {
        if (Authentification::estConnecte()) {
            header('Location: index.php?route=tableau-de-bord');
            exit;
        }

        $erreur = null;
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
                    $_SESSION['utilisateur'] = $utilisateur;
                    Flash::success('Connexion réussie. Bienvenue ' . $utilisateur['prenom'] . ' !');
                    header('Location: index.php?route=tableau-de-bord');
                    exit;
                } else {
                    $erreur = 'Email ou mot de passe incorrect.';
                }
            }
        }
        $twig = TwigHelper::getInstance();
        echo $twig->render('auth/connexion.twig', [
            'erreur' => $erreur
        ]);
    }

    public function deconnexion(): void
    {
        session_destroy();
        Flash::info('Vous avez été déconnecté.');
        header('Location: index.php?route=auth/connexion');
        exit;
    }
}
