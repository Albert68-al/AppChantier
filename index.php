<?php
// index.php

require_once 'config/init.php';

$auth = new Auth();

// Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
if (!$auth->isLoggedIn()) {
    header('Location: views/auth/login.php');
    exit;
}

// Router simple
$controller = $_GET['controller'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$requestedAction = $action;

// Liste des contrôleurs autorisés
$allowedControllers = [
    'dashboard', 'chantier', 'employe', 'materiau', 
    'finance', 'user', 'rapport'
];

// Vérifier si le contrôleur est autorisé
if (!in_array($controller, $allowedControllers)) {
    $controller = 'dashboard';
}

// Construire le chemin du contrôleur
$controllerFile = "controllers/" . ucfirst($controller) . "Controller.php";

// Inclure le contrôleur
if (file_exists($controllerFile)) {
    unset($_GET['action']);
    require_once $controllerFile;
    $_GET['action'] = $requestedAction;
} else {
    // Rediriger vers le dashboard par défaut
    header('Location: views/dashboard/index.php');
    exit;
}

$controllerClass = ucfirst($controller) . 'Controller';
if (!class_exists($controllerClass)) {
    header('Location: views/dashboard/index.php');
    exit;
}

$controllerInstance = new $controllerClass();

if (!method_exists($controllerInstance, $action)) {
    $action = 'index';
}

$id = $_GET['id'] ?? null;

if ($id !== null) {
    $data = $controllerInstance->$action($id);
} else {
    $data = $controllerInstance->$action();
}

$GLOBALS['data'] = is_array($data) ? $data : [];

$viewFolders = [
    'dashboard' => 'dashboard',
    'chantier' => 'chantiers',
    'employe' => 'employes',
    'materiau' => 'materiaux',
    'finance' => 'finances',
    'user' => 'users',
    'rapport' => 'repports'
];

$viewFolder = $viewFolders[$controller] ?? 'dashboard';
$viewFile = __DIR__ . '/views/' . $viewFolder . '/' . $action . '.php';

if (!file_exists($viewFile)) {
    $fallbackView = __DIR__ . '/views/' . $viewFolder . '/index.php';
    if (file_exists($fallbackView)) {
        require_once $fallbackView;
        exit;
    }

    require_once __DIR__ . '/views/dashboard/index.php';
    exit;
}

require_once $viewFile;