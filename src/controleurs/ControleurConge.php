<?php

namespace App\Controleurs;

use App\Middleware\Authentification;
use App\Helpers\TwigHelper;
use App\Modeles\DemandeConge;
use App\Modeles\TypeConge;
use App\Helpers\Flash;
use App\Helpers\Validation;

class ControleurConge
{
    
     //Afficher le formulaire de création de demande
    public function creer(): void
    {
        Authentification::verifierConnexion();
        
        $utilisateur = Authentification::getUtilisateur();
        $typesConge = TypeConge::listerActifs();
        $erreurs = [];

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnees = [
                'type_conge_id' => (int) ($_POST['type_conge_id'] ?? 0),
                'date_debut' => Validation::nettoyer($_POST['date_debut'] ?? ''),
                'date_fin' => Validation::nettoyer($_POST['date_fin'] ?? ''),
                'demi_journee' => $_POST['demi_journee'] ?? 'NONE',
                'motif' => Validation::nettoyer($_POST['motif'] ?? ''),
                'commentaire' => Validation::nettoyer($_POST['commentaire'] ?? '')
            ];

            // Validation
            if (empty($donnees['type_conge_id'])) {
                $erreurs[] = 'Veuillez sélectionner un type de congé.';
            }
            if (!Validation::date($donnees['date_debut'])) {
                $erreurs[] = 'Date de début invalide.';
            }
            if (!Validation::date($donnees['date_fin'])) {
                $erreurs[] = 'Date de fin invalide.';
            }
            if (!Validation::dateFinApresDebut($donnees['date_debut'], $donnees['date_fin'])) {
                $erreurs[] = 'La date de fin doit être après la date de début.';
            }

            // Vérifier demi-journée
            if ($donnees['demi_journee'] !== 'NONE' && $donnees['date_debut'] !== $donnees['date_fin']) {
                $erreurs[] = 'La demi-journée n\'est autorisée que si la date de début et de fin sont identiques.';
            }

            // Vérifier le type de congé
            $typeConge = TypeConge::trouverParId($donnees['type_conge_id']);
            if (!$typeConge || !$typeConge['actif']) {
                $erreurs[] = 'Type de congé invalide.';
            }

            // Vérifier justificatif obligatoire
            $justificatif = null;
            if ($typeConge && $typeConge['justificatif_obligatoire']) {
                if (!isset($_FILES['justificatif']) || $_FILES['justificatif']['error'] !== UPLOAD_ERR_OK) {
                    $erreurs[] = 'Un justificatif est obligatoire pour ce type de congé.';
                } else {
                    // Upload du justificatif
                    $justificatif = $this->uploadJustificatif($_FILES['justificatif']);
                    if (!$justificatif) {
                        $erreurs[] = 'Erreur lors de l\'upload du justificatif.';
                    }
                }
            } elseif (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
                // Justificatif optionnel
                $justificatif = $this->uploadJustificatif($_FILES['justificatif']);
            }

            // Vérifier chevauchement
            if (empty($erreurs) && DemandeConge::verifierChevauchement($utilisateur['id'], $donnees['date_debut'], $donnees['date_fin'])) {
                $erreurs[] = 'Vous avez déjà une demande de congé en attente ou acceptée sur cette période.';
            }

            // Créer la demande si pas d'erreurs
            if (empty($erreurs)) {
                $donnees['utilisateur_id'] = $utilisateur['id'];
                $donnees['justificatif'] = $justificatif;
                
                $id = DemandeConge::creer($donnees);
                Flash::success('Votre demande de congé a été créée avec succès.');
                header('Location: index.php?route=conge/voir&id=' . $id);
                exit;
            }
        }

        $twig = TwigHelper::getInstance();
        echo $twig->render('conges/creer.twig', [
            'typesConge' => $typesConge,
            'erreurs' => $erreurs ?? [],
            'donnees' => $donnees ?? []
        ]);
    }

    public function mesDemandes(): void
    {
        Authentification::verifierConnexion();
        
        $utilisateur = Authentification::getUtilisateur();
        $demandes = DemandeConge::listerParUtilisateur($utilisateur['id']);

        $twig = TwigHelper::getInstance();
        echo $twig->render('conges/mes_demandes.twig', [
            'demandes' => $demandes
        ]);
    }

    public function voir(): void
    {
        Authentification::verifierConnexion();
        
        $id = (int) ($_GET['id'] ?? 0);
        $demande = DemandeConge::trouverParId($id);
        $utilisateur = Authentification::getUtilisateur();

        if (!$demande) {
            Flash::error('Demande introuvable.');
            header('Location: index.php?route=conge/mes-demandes');
            exit;
        }

        // Vérifier que l'utilisateur peut voir cette demande
        if ($demande['utilisateur_id'] !== $utilisateur['id'] && $utilisateur['role'] !== ROLE_MANAGER) {
            Flash::error('Accès non autorisé.');
            header('Location: index.php?route=tableau-de-bord');
            exit;
        }

        $twig = TwigHelper::getInstance();
        echo $twig->render('conges/voir.twig', [
            'demande' => $demande
        ]);
    }

    public function annuler(): void
    {
        Authentification::verifierConnexion();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::error('Méthode non autorisée.');
            header('Location: index.php?route=conge/mes-demandes');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $demande = DemandeConge::trouverParId($id);
        $utilisateur = Authentification::getUtilisateur();

        if (!$demande) {
            Flash::error('Demande introuvable.');
        } elseif ($demande['utilisateur_id'] !== $utilisateur['id']) {
            Flash::error('Vous ne pouvez annuler que vos propres demandes.');
        } elseif ($demande['statut'] !== STATUT_EN_ATTENTE) {
            Flash::error('Seules les demandes en attente peuvent être annulées.');
        } else {
            if (DemandeConge::annuler($id)) {
                Flash::success('Demande annulée avec succès.');
            } else {
                Flash::error('Erreur lors de l\'annulation.');
            }
        }

        header('Location: index.php?route=conge/mes-demandes');
        exit;
    }

    private function uploadJustificatif(array $fichier): ?string
    {
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensionsAutorisees)) {
            return null;
        }

        // Vérifier la taille (max 5 Mo)
        if ($fichier['size'] > 5 * 1024 * 1024) {
            return null;
        }

        // Créer le dossier uploads s'il n'existe pas
        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        // Générer un nom unique
        $nomFichier = uniqid('justificatif_', true) . '.' . $extension;
        $cheminComplet = UPLOAD_PATH . '/' . $nomFichier;

        if (move_uploaded_file($fichier['tmp_name'], $cheminComplet)) {
            return $nomFichier;
        }

        return null;
    }
}
