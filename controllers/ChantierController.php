<?php
// controllers/ChantierController.php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Chantier.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/User.php';

class ChantierController {
    private $chantierModel;
    private $employeModel;
    private $userModel;
    private $auth;
    
    public function __construct() {
        $this->chantierModel = new Chantier();
        $this->employeModel = new Employe();
        $this->userModel = new User();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }
    
    public function index() {
        $filters = [];
        
        if (isset($_GET['statut']) && !empty($_GET['statut'])) {
            $filters['statut'] = $_GET['statut'];
        }
        
        if (isset($_GET['client']) && !empty($_GET['client'])) {
            $filters['client'] = $_GET['client'];
        }
        
        $chantiers = $this->chantierModel->getAll($filters);
        $chefs = $this->userModel->getChefs();
        
        return [
            'chantiers' => $chantiers,
            'chefs' => $chefs,
            'filters' => $filters
        ];
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'],
                    'localisation' => $_POST['localisation'],
                    'client' => $_POST['client'],
                    'budget_total' => $_POST['budget_total'],
                    'date_debut' => $_POST['date_debut'],
                    'date_fin' => $_POST['date_fin'] ?? null,
                    'statut' => $_POST['statut'],
                    'chef_chantier_id' => $_POST['chef_chantier_id'] ?? null,
                    'description' => $_POST['description'] ?? ''
                ];
                
                if ($this->chantierModel->create($data)) {
                    $_SESSION['success'] = "Chantier créé avec succès !";
                    header('Location: index.php?controller=chantier&action=index');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $chefs = $this->userModel->getChefs();
        return ['chefs' => $chefs];
    }
    
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'],
                    'localisation' => $_POST['localisation'],
                    'client' => $_POST['client'],
                    'budget_total' => $_POST['budget_total'],
                    'date_debut' => $_POST['date_debut'],
                    'date_fin' => $_POST['date_fin'] ?? null,
                    'statut' => $_POST['statut'],
                    'chef_chantier_id' => $_POST['chef_chantier_id'] ?? null,
                    'description' => $_POST['description'] ?? ''
                ];
                
                if ($this->chantierModel->update($id, $data)) {
                    $_SESSION['success'] = "Chantier modifié avec succès !";
                    header('Location: index.php?controller=chantier&action=view&id=' . $id);
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $chantier = $this->chantierModel->getById($id);
        $chefs = $this->userModel->getChefs();
        
        return [
            'chantier' => $chantier,
            'chefs' => $chefs
        ];
    }
    
    public function view($id) {
        $chantier = $this->chantierModel->getById($id);
        
        if (!$chantier) {
            $_SESSION['error'] = "Chantier non trouvé !";
            header('Location: index.php?controller=chantier&action=index');
            exit;
        }
        
        // Récupérer les employés affectés
        $sql = "SELECT e.*, a.date_debut, a.date_fin 
                FROM affectations a 
                JOIN employes e ON a.employe_id = e.id 
                WHERE a.chantier_id = :chantier_id 
                ORDER BY a.date_debut DESC";
        
        $stmt = Database::getInstance()->getConnection()->prepare($sql);
        $stmt->execute([':chantier_id' => $id]);
        $employes = $stmt->fetchAll();
        
        // Calculer les statistiques financières
        $depenses = $this->chantierModel->getDepenses($id);
        $paiements = $this->chantierModel->getPaiements($id);
        $solde = $chantier['budget_total'] - $depenses;
        $pourcentage_utilisation = $chantier['budget_total'] > 0 ? 
            ($depenses / $chantier['budget_total']) * 100 : 0;
        
        return [
            'chantier' => $chantier,
            'employes' => $employes,
            'depenses' => $depenses,
            'paiements' => $paiements,
            'solde' => $solde,
            'pourcentage_utilisation' => $pourcentage_utilisation
        ];
    }
    
    public function delete($id) {
        if ($this->auth->hasRole('admin')) {
            try {
                if ($this->chantierModel->delete($id)) {
                    $_SESSION['success'] = "Chantier supprimé avec succès !";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Action non autorisée !";
        }
        
        header('Location: index.php?controller=chantier&action=index');
        exit;
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $controller = new ChantierController();
    
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
        case 'index':
        default:
            $data = $controller->index();
            break;
    }
}
?>