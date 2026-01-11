<?php
// controllers/AuthController.php

require_once __DIR__ . '/../config/init.php';

class AuthController {
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = Database::sanitize($_POST['username']);
            $password = $_POST['password'];
            
            if ($this->auth->login($username, $password)) {
                $_SESSION['success'] = "Connexion réussie !";
                header('Location: ../views/dashboard/index.php');
                exit;
            } else {
                $_SESSION['error'] = "Identifiants incorrects !";
                header('Location: ../views/auth/login.php');
                exit;
            }
        }
    }
    
    public function logout() {
        $this->auth->logout();
    }
    
    public function checkAccess($requiredRole = null) {
        if ($requiredRole) {
            $this->auth->requireRole($requiredRole);
        } else {
            $this->auth->requireAuth();
        }
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $authController = new AuthController();
    
    switch ($_GET['action']) {
        case 'login':
            $authController->login();
            break;
        case 'logout':
            $authController->logout();
            break;
    }
}
?>