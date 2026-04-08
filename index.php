<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/configuration.php';

require_once __DIR__ . '/vendor/autoload.php';

$route = $_GET['route'] ?? 'tableau-de-bord';

if ($route !== 'auth/connexion' && $route !== 'auth/deconnexion') {
    if (!\App\Middleware\Authentification::estConnecte()) {
        header('Location: index.php?route=auth/connexion');
        exit;
    }
    $twig = \App\Helpers\TwigHelper::getInstance();
    $utilisateur = \App\Middleware\Authentification::getUtilisateur();
    $twig->addGlobal('utilisateur', $utilisateur);
    
    if ($utilisateur['role'] === ROLE_MANAGER) {
        $demandesEnAttente = \App\Modeles\DemandeConge::listerEnAttente();
        $twig->addGlobal('nb_demandes_attente', count($demandesEnAttente));
    }
}

$routeParts = explode('/', $route);
$controllerName = $routeParts[0] ?? 'tableau-de-bord';
$actionName = $routeParts[1] ?? 'index';

$controllerMap = [
    'auth' => 'ControleurAuth',
    'tableau-de-bord' => 'ControleurTableauDeBord',
    'conge' => 'ControleurConge',
    'manager' => 'ControleurManager'
];

// Mapping des actions
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

if ($controllerName === 'manager' && isset($routeParts[1])) {
    $subRoute = $routeParts[1];
    
    if ($subRoute === 'conge' && isset($routeParts[2])) {
        $actionName = $routeParts[2] === 'accepter' ? 'accepterConge' : 'refuserConge';
    }
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
    if (isset($actionMap[$controllerName][$actionName])) {
        $actionName = $actionMap[$controllerName][$actionName];
    }
}

$controllerClass = 'App\\Controleurs\\' . ($controllerMap[$controllerName] ?? 'ControleurTableauDeBord');

if (!class_exists($controllerClass)) {
    die('Contrôleur introuvable : ' . $controllerClass);
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionName)) {
    die('Action introuvable : ' . $actionName . ' dans ' . $controllerClass);
}

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


