<?php
/**
 * Point d'entrée de l'application
 * Routing simple via query string
 * BTS SIO SLAM - Gestion des congés
 */

// Activer l'affichage des erreurs en développement
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Charger la configuration
require_once dirname(__DIR__) . '/config/configuration.php';

// Charger Composer (doit être chargé en premier pour l'autoload)
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Récupérer la route depuis la query string
$route = $_GET['route'] ?? 'tableau-de-bord';

// Si non connecté et route différente de connexion, rediriger vers connexion
if ($route !== 'auth/connexion' && $route !== 'auth/deconnexion') {
    if (!\App\Middleware\Authentification::estConnecte()) {
        header('Location: index.php?route=auth/connexion');
        exit;
    }
    // Ajouter l'utilisateur connecté aux variables Twig globales
    $twig = \App\Helpers\TwigHelper::getInstance();
    $utilisateur = \App\Middleware\Authentification::getUtilisateur();
    $twig->addGlobal('utilisateur', $utilisateur);
    
    // Si c'est un manager, ajouter le nombre de demandes en attente pour la notification
    if ($utilisateur['role'] === ROLE_MANAGER) {
        $demandesEnAttente = \App\Modeles\DemandeConge::listerEnAttente();
        $twig->addGlobal('nb_demandes_attente', count($demandesEnAttente));
    }
}

// Router vers le bon contrôleur
$routeParts = explode('/', $route);
$controllerName = $routeParts[0] ?? 'tableau-de-bord';
$actionName = $routeParts[1] ?? 'index';

// Mapping simple des routes vers les contrôleurs et actions
$controllerMap = [
    'auth' => 'ControleurAuth',
    'tableau-de-bord' => 'ControleurTableauDeBord',
    'conge' => 'ControleurConge',
    'manager' => 'ControleurManager'
];

// Mapping des actions (nom route => nom méthode)
$actionMap = [
    'auth' => [
        'connexion' => 'connexion',
        'deconnexion' => 'deconnexion'
    ],
    'tableau-de-bord' => [
        'index' => 'index'
    ],
    'conge' => [
        'creer' => 'creer',
        'mes-demandes' => 'mesDemandes',
        'voir' => 'voir',
        'annuler' => 'annuler'
    ],
    'manager' => [
        'demandes' => 'demandes',
        'employes' => 'employes',
        'types-conges' => 'typesConges'
    ]
];

// Gestion spéciale pour les routes manager avec sous-routes
if ($controllerName === 'manager' && isset($routeParts[1])) {
    $subRoute = $routeParts[1];
    
    // Routes manager/conge/accepter ou manager/conge/refuser
    if ($subRoute === 'conge' && isset($routeParts[2])) {
        $actionName = $routeParts[2] === 'accepter' ? 'accepterConge' : 'refuserConge';
    }
    // Routes manager/employes/creer, modifier, supprimer
    elseif ($subRoute === 'employes' && isset($routeParts[2])) {
        switch ($routeParts[2]) {
            case 'creer':
                $actionName = 'creerEmploye';
                break;
            case 'modifier':
                $actionName = 'modifierEmploye';
                break;
            case 'supprimer':
                $actionName = 'supprimerEmploye';
                break;
            default:
                $actionName = 'employes';
        }
    }
    // Routes manager/types-conges/creer, modifier, supprimer
    elseif ($subRoute === 'types-conges' && isset($routeParts[2])) {
        switch ($routeParts[2]) {
            case 'creer':
                $actionName = 'creerTypeConge';
                break;
            case 'modifier':
                $actionName = 'modifierTypeConge';
                break;
            case 'supprimer':
                $actionName = 'supprimerTypeConge';
                break;
            default:
                $actionName = 'typesConges';
        }
    }
    // Routes simples manager/demandes, manager/employes, manager/types-conges
    else {
        switch ($subRoute) {
            case 'demandes':
                $actionName = 'demandes';
                break;
            case 'employes':
                $actionName = 'employes';
                break;
            case 'types-conges':
                $actionName = 'typesConges';
                break;
            default:
                $actionName = 'demandes';
        }
    }
} else {
    // Pour les autres routes, utiliser le mapping
    if (isset($actionMap[$controllerName][$actionName])) {
        $actionName = $actionMap[$controllerName][$actionName];
    }
}

// Déterminer le nom du contrôleur
$controllerClass = 'App\\Controleurs\\' . ($controllerMap[$controllerName] ?? 'ControleurTableauDeBord');

// Vérifier que la classe existe
if (!class_exists($controllerClass)) {
    die('Contrôleur introuvable : ' . $controllerClass);
}

// Instancier le contrôleur
$controller = new $controllerClass();

// Vérifier que la méthode existe
if (!method_exists($controller, $actionName)) {
    die('Action introuvable : ' . $actionName . ' dans ' . $controllerClass);
}

// Exécuter l'action
try {
    $controller->$actionName();
} catch (\Exception $e) {
    echo '<h1>Erreur</h1>';
    echo '<p><strong>Message :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Fichier :</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Ligne :</strong> ' . $e->getLine() . '</p>';
    echo '<h2>Trace :</h2>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    exit;
}
