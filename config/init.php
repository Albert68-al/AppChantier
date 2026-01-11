<?php
// config/init.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusion des dépendances
require_once __DIR__ . '/database.php';

// Gestion des sessions et authentification
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = :username AND status = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Mettre à jour la dernière connexion
            $updateSql = "UPDATE users SET last_login = datetime('now') WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([':id' => $user['id']]);
            
            // Stocker les infos utilisateur en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom_complet'] = $user['prenom'] . ' ' . $user['nom'];
            
            return true;
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: ../views/auth/login.php');
            exit;
        }
    }
    
    public function requireRole($role) {
        $this->requireAuth();
        if (!$this->hasRole($role)) {
            $_SESSION['error'] = "Accès refusé. Rôle requis : " . $role;
            header('Location: ../views/dashboard/index.php');
            exit;
        }
    }
}

// Initialiser l'authentification
$auth = new Auth();
?>