<?php

namespace App\Controleurs;

use App\Middleware\Authentification;
use App\Helpers\TwigHelper;
use App\Modeles\DemandeConge;
use App\Modeles\Utilisateur;
use App\Modeles\TypeConge;
use App\Helpers\Flash;
use App\Helpers\Validation;

class ControleurManager
{
    //Lister les demandes en attente
     
    public function demandes(): void
    {
        Authentification::verifierManager();
        
        $demandes = DemandeConge::listerEnAttente();

        $twig = TwigHelper::getInstance();
        echo $twig->render('manager/demandes.twig', [
            'demandes' => $demandes
        ]);
    }

    /**
     * Accepter une demande
     */
    public function accepterConge(): void
    {
        Authentification::verifierManager();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::error('Méthode non autorisée.');
            header('Location: index.php?route=manager/demandes');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $commentaire = Validation::nettoyer($_POST['commentaire_decision'] ?? '');
        $manager = Authentification::getUtilisateur();

        $demande = DemandeConge::trouverParId($id);
        if (!$demande) {
            Flash::error('Demande introuvable.');
        } elseif ($demande['statut'] !== STATUT_EN_ATTENTE) {
            Flash::error('Cette demande a déjà été traitée.');
        } else {
            if (DemandeConge::accepter($id, $manager['id'], $commentaire)) {
                Flash::success('Demande acceptée avec succès.');
            } else {
                Flash::error('Erreur lors de l\'acceptation.');
            }
        }

        header('Location: index.php?route=manager/demandes');
        exit;
    }

    /**
     * Refuser une demande
     */
    public function refuserConge(): void
    {
        Authentification::verifierManager();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::error('Méthode non autorisée.');
            header('Location: index.php?route=manager/demandes');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $commentaire = Validation::nettoyer($_POST['commentaire_decision'] ?? '');
        $manager = Authentification::getUtilisateur();

        $demande = DemandeConge::trouverParId($id);
        if (!$demande) {
            Flash::error('Demande introuvable.');
        } elseif ($demande['statut'] !== STATUT_EN_ATTENTE) {
            Flash::error('Cette demande a déjà été traitée.');
        } else {
            if (DemandeConge::refuser($id, $manager['id'], $commentaire)) {
                Flash::success('Demande refusée.');
            } else {
                Flash::error('Erreur lors du refus.');
            }
        }

        header('Location: index.php?route=manager/demandes');
        exit;
    }

    /**
     * Lister les employés
     */
    public function employes(): void
    {
        Authentification::verifierManager();
        
        $employes = Utilisateur::listerTous();

        $twig = TwigHelper::getInstance();
        echo $twig->render('manager/employes.twig', [
            'employes' => $employes
        ]);
    }

    /**
     * Créer un employé
     */
    public function creerEmploye(): void
    {
        Authentification::verifierManager();
        
        $erreurs = [];
        $donnees = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnees = [
                'nom' => Validation::nettoyer($_POST['nom'] ?? ''),
                'prenom' => Validation::nettoyer($_POST['prenom'] ?? ''),
                'email' => Validation::nettoyer($_POST['email'] ?? ''),
                'service' => Validation::nettoyer($_POST['service'] ?? ''),
                'poste' => Validation::nettoyer($_POST['poste'] ?? ''),
                'date_embauche' => Validation::nettoyer($_POST['date_embauche'] ?? ''),
                'role' => $_POST['role'] ?? 'EMPLOYE',
                'mot_de_passe' => $_POST['mot_de_passe'] ?? ''
            ];

            // Validation
            if (empty($donnees['nom'])) $erreurs[] = 'Le nom est obligatoire.';
            if (empty($donnees['prenom'])) $erreurs[] = 'Le prénom est obligatoire.';
            if (empty($donnees['email']) || !Validation::email($donnees['email'])) {
                $erreurs[] = 'Email invalide.';
            }
            if (empty($donnees['service'])) $erreurs[] = 'Le service est obligatoire.';
            if (empty($donnees['poste'])) $erreurs[] = 'Le poste est obligatoire.';
            if (!Validation::date($donnees['date_embauche'])) {
                $erreurs[] = 'Date d\'embauche invalide.';
            }
            if (empty($donnees['mot_de_passe']) || strlen($donnees['mot_de_passe']) < 6) {
                $erreurs[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
            if (!in_array($donnees['role'], [ROLE_EMPLOYE, ROLE_MANAGER])) {
                $erreurs[] = 'Rôle invalide.';
            }

            // Vérifier si l'email existe déjà
            if (empty($erreurs) && Utilisateur::trouverParEmail($donnees['email'])) {
                $erreurs[] = 'Cet email est déjà utilisé.';
            }

            if (empty($erreurs)) {
                $id = Utilisateur::creer($donnees);
                Flash::success('Employé créé avec succès.');
                header('Location: index.php?route=manager/employes');
                exit;
            }
        }

        $twig = TwigHelper::getInstance();
        echo $twig->render('manager/formulaire_employe.twig', [
            'erreurs' => $erreurs,
            'donnees' => $donnees,
            'action' => 'creer'
        ]);
    }

    /**
     * Modifier un employé
     */
    public function modifierEmploye(): void
    {
        Authentification::verifierManager();
        
        $id = (int) ($_GET['id'] ?? 0);
        $employe = Utilisateur::trouverParId($id);
        
        if (!$employe) {
            Flash::error('Employé introuvable.');
            header('Location: index.php?route=manager/employes');
            exit;
        }

        $erreurs = [];
        $donnees = $employe;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnees = [
                'nom' => Validation::nettoyer($_POST['nom'] ?? ''),
                'prenom' => Validation::nettoyer($_POST['prenom'] ?? ''),
                'email' => Validation::nettoyer($_POST['email'] ?? ''),
                'service' => Validation::nettoyer($_POST['service'] ?? ''),
                'poste' => Validation::nettoyer($_POST['poste'] ?? ''),
                'date_embauche' => Validation::nettoyer($_POST['date_embauche'] ?? ''),
                'role' => $_POST['role'] ?? 'EMPLOYE',
                'mot_de_passe' => $_POST['mot_de_passe'] ?? ''
            ];

            // Validation
            if (empty($donnees['nom'])) $erreurs[] = 'Le nom est obligatoire.';
            if (empty($donnees['prenom'])) $erreurs[] = 'Le prénom est obligatoire.';
            if (empty($donnees['email']) || !Validation::email($donnees['email'])) {
                $erreurs[] = 'Email invalide.';
            }
            if (empty($donnees['service'])) $erreurs[] = 'Le service est obligatoire.';
            if (empty($donnees['poste'])) $erreurs[] = 'Le poste est obligatoire.';
            if (!Validation::date($donnees['date_embauche'])) {
                $erreurs[] = 'Date d\'embauche invalide.';
            }
            if (!in_array($donnees['role'], [ROLE_EMPLOYE, ROLE_MANAGER])) {
                $erreurs[] = 'Rôle invalide.';
            }

            // Vérifier si l'email existe déjà (sauf pour cet utilisateur)
            $autreUtilisateur = Utilisateur::trouverParEmail($donnees['email']);
            if (empty($erreurs) && $autreUtilisateur && $autreUtilisateur['id'] != $id) {
                $erreurs[] = 'Cet email est déjà utilisé.';
            }

            // Mot de passe optionnel lors de la modification
            if (!empty($donnees['mot_de_passe']) && strlen($donnees['mot_de_passe']) < 6) {
                $erreurs[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }

            if (empty($erreurs)) {
                // Si pas de nouveau mot de passe, ne pas le modifier
                if (empty($donnees['mot_de_passe'])) {
                    unset($donnees['mot_de_passe']);
                }
                
                if (Utilisateur::modifier($id, $donnees)) {
                    Flash::success('Employé modifié avec succès.');
                    header('Location: index.php?route=manager/employes');
                    exit;
                } else {
                    $erreurs[] = 'Erreur lors de la modification.';
                }
            }
        }

        $twig = TwigHelper::getInstance();
        echo $twig->render('manager/formulaire_employe.twig', [
            'erreurs' => $erreurs,
            'donnees' => $donnees,
            'action' => 'modifier',
            'id' => $id
        ]);
    }

    /**
     * Supprimer un employé
     */
    public function supprimerEmploye(): void
    {
        Authentification::verifierManager();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::error('Méthode non autorisée.');
            header('Location: index.php?route=manager/employes');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $employe = Utilisateur::trouverParId($id);

        if (!$employe) {
            Flash::error('Employé introuvable.');
        } elseif ($employe['id'] == Authentification::getUtilisateur()['id']) {
            Flash::error('Vous ne pouvez pas supprimer votre propre compte.');
        } else {
            if (Utilisateur::supprimer($id)) {
                Flash::success('Employé supprimé avec succès.');
            } else {
                Flash::error('Erreur lors de la suppression.');
            }
        }

        header('Location: index.php?route=manager/employes');
        exit;
    }

    /**
     * Lister les types de congés
     */
    public function typesConges(): void
    {
        Authentification::verifierManager();
        
        $types = TypeConge::listerTous();

        $twig = TwigHelper::getInstance();
        echo $twig->render('manager/types_conges.twig', [
            'types' => $types
        ]);
    }

    /**
     * Créer un type de congé
     */
    public function creerTypeConge(): void
    {
        Authentification::verifierManager();
        
        $erreurs = [];
        $donnees = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnees = [
                'code' => strtoupper(Validation::nettoyer($_POST['code'] ?? '')),
                'libelle' => Validation::nettoyer($_POST['libelle'] ?? '')
            ];
            
            // Ne définir les checkboxes que si elles sont cochées
            if (isset($_POST['justificatif_obligatoire'])) {
                $donnees['justificatif_obligatoire'] = true;
            }
            if (isset($_POST['actif'])) {
                $donnees['actif'] = true;
            }

            // Validation
            if (empty($donnees['code'])) $erreurs[] = 'Le code est obligatoire.';
            if (empty($donnees['libelle'])) $erreurs[] = 'Le libellé est obligatoire.';

            if (empty($erreurs)) {
                $id = TypeConge::creer($donnees);
                Flash::success('Type de congé créé avec succès.');
                header('Location: index.php?route=manager/types-conges');
                exit;
            }
        }

        $twig = TwigHelper::getInstance();
        echo $twig->render('manager/formulaire_type_conge.twig', [
            'erreurs' => $erreurs,
            'donnees' => $donnees,
            'action' => 'creer'
        ]);
    }

    /**
     * Modifier un type de congé
     */
    public function modifierTypeConge(): void
    {
        Authentification::verifierManager();
        
        // Récupérer l'ID depuis GET ou POST
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        
        if ($id === 0) {
            Flash::error('ID du type de congé manquant.');
            header('Location: index.php?route=manager/types-conges');
            exit;
        }
        
        $type = TypeConge::trouverParId($id);
        
        if (!$type) {
            Flash::error('Type de congé introuvable.');
            header('Location: index.php?route=manager/types-conges');
            exit;
        }

        $erreurs = [];
        $donnees = $type;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnees = [
                'code' => strtoupper(Validation::nettoyer($_POST['code'] ?? '')),
                'libelle' => Validation::nettoyer($_POST['libelle'] ?? ''),
                'justificatif_obligatoire' => isset($_POST['justificatif_obligatoire']) ? true : false,
                'actif' => isset($_POST['actif']) ? true : false
            ];

            // Validation
            if (empty($donnees['code'])) {
                $erreurs[] = 'Le code est obligatoire.';
            } else {
                // Vérifier si le code existe déjà pour un autre type de congé
                $db = \App\Modeles\BaseDeDonnees::getInstance();
                $stmtCheck = $db->prepare('SELECT id FROM type_conge WHERE code = ? AND id != ?');
                $stmtCheck->execute([$donnees['code'], $id]);
                if ($stmtCheck->fetch()) {
                    $erreurs[] = 'Ce code est déjà utilisé par un autre type de congé.';
                }
            }
            
            if (empty($donnees['libelle'])) {
                $erreurs[] = 'Le libellé est obligatoire.';
            }

            if (empty($erreurs)) {
                if (TypeConge::modifier($id, $donnees)) {
                    Flash::success('Type de congé modifié avec succès.');
                    header('Location: index.php?route=manager/types-conges');
                    exit;
                } else {
                    $erreurs[] = 'Erreur lors de la modification. Veuillez réessayer.';
                }
            }
        }

        $twig = TwigHelper::getInstance();
        echo $twig->render('manager/formulaire_type_conge.twig', [
            'erreurs' => $erreurs,
            'donnees' => $donnees,
            'action' => 'modifier',
            'id' => $id
        ]);
    }

    /**
     * Supprimer un type de congé
     */
    public function supprimerTypeConge(): void
    {
        Authentification::verifierManager();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::error('Méthode non autorisée.');
            header('Location: index.php?route=manager/types-conges');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);

        $result = TypeConge::supprimer($id);
        if ($result === true) {
            Flash::success('Type de congé supprimé avec succès.');
        } else {
            // $result contient le message d'erreur
            Flash::error(is_string($result) ? $result : 'Erreur lors de la suppression.');
        }

        header('Location: index.php?route=manager/types-conges');
        exit;
    }
}
