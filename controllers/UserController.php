<?php
// controllers/UserController.php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;
    private $auth;
    
    public function __construct() {
        $this->userModel = new User();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }
    
    public function index() {
        $this->auth->requireRole('admin');
        $users = $this->userModel->getAll();
        
        return ['users' => $users];
    }
    
    public function create() {
        $this->auth->requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validation
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    throw new Exception("Les mots de passe ne correspondent pas !");
                }
                
                $data = [
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'email' => $_POST['email'],
                    'role' => $_POST['role'],
                    'telephone' => $_POST['telephone'] ?? null
                ];
                
                if ($this->userModel->create($data)) {
                    $_SESSION['success'] = "Utilisateur créé avec succès !";
                    header('Location: index.php?controller=user&action=index');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        return [];
    }
    
    public function edit($id) {
        $this->auth->requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'email' => $_POST['email'],
                    'role' => $_POST['role'],
                    'telephone' => $_POST['telephone'] ?? null
                ];
                
                // Si un nouveau mot de passe est fourni
                if (!empty($_POST['password'])) {
                    if ($_POST['password'] !== $_POST['confirm_password']) {
                        throw new Exception("Les mots de passe ne correspondent pas !");
                    }
                    $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                
                if ($this->userModel->update($id, $data)) {
                    $_SESSION['success'] = "Utilisateur modifié avec succès !";
                    header('Location: index.php?controller=user&action=index');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé !";
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        
        return ['user' => $user];
    }
    
    public function delete($id) {
        $this->auth->requireRole('admin');
        // Empêcher l'auto-suppression
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte !";
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        
        try {
            if ($this->userModel->delete($id)) {
                $_SESSION['success'] = "Utilisateur désactivé avec succès !";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
        }
        
        header('Location: index.php?controller=user&action=index');
        exit;
    }
    
    public function view($id) {
        $this->auth->requireRole('admin');

        $user = $this->userModel->getById($id);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé !";
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        return ['user' => $user];
    }
    
    public function profile() {
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'email' => $_POST['email'],
                    'telephone' => $_POST['telephone'] ?? null
                ];
                
                // Vérifier l'ancien mot de passe si un nouveau est fourni
                if (!empty($_POST['new_password'])) {
                    if (!password_verify($_POST['current_password'], $user['password'])) {
                        throw new Exception("Mot de passe actuel incorrect !");
                    }
                    
                    if ($_POST['new_password'] !== $_POST['confirm_password']) {
                        throw new Exception("Les nouveaux mots de passe ne correspondent pas !");
                    }
                    
                    $data['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                }
                
                if ($this->userModel->update($_SESSION['user_id'], $data)) {
                    $_SESSION['success'] = "Profil mis à jour avec succès !";
                    $_SESSION['nom_complet'] = $data['prenom'] . ' ' . $data['nom'];
                    header('Location: index.php?controller=user&action=profile');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        return ['user' => $user];
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $controller = new UserController();
    
    switch ($_GET['action']) {
        case 'create':
            $data = $controller->create();
            break;
        case 'edit':
            $id = $_GET['id'] ?? 0;
            $data = $controller->edit($id);
            break;
        case 'view':
            $id = $_GET['id'] ?? 0;
            $data = $controller->view($id);
            break;
        case 'delete':
            $id = $_GET['id'] ?? 0;
            $controller->delete($id);
            break;
        case 'profile':
            $data = $controller->profile();
            break;
        case 'index':
        default:
            $data = $controller->index();
            break;
    }
}
?>