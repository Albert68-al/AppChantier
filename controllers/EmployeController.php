<?php
// controllers/EmployeController.php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/Chantier.php';

class EmployeController {
    private $employeModel;
    private $chantierModel;
    private $auth;
    
    public function __construct() {
        $this->employeModel = new Employe();
        $this->chantierModel = new Chantier();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }
    
    public function index() {
        $filters = [];
        
        if (isset($_GET['fonction']) && !empty($_GET['fonction'])) {
            $filters['fonction'] = $_GET['fonction'];
        }
        
        if (isset($_GET['statut']) && !empty($_GET['statut'])) {
            $filters['statut'] = $_GET['statut'];
        }
        
        $employes = $this->employeModel->getAll($filters);
        
        return [
            'employes' => $employes,
            'filters' => $filters
        ];
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'email' => $_POST['email'] ?? null,
                    'telephone' => $_POST['telephone'] ?? null,
                    'fonction' => $_POST['fonction'],
                    'salaire_journalier' => $_POST['salaire_journalier'],
                    'date_embauche' => $_POST['date_embauche'],
                    'statut' => $_POST['statut']
                ];
                
                if ($this->employeModel->create($data)) {
                    $_SESSION['success'] = "Employé créé avec succès !";
                    header('Location: index.php?controller=employe&action=index');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        return [];
    }
    
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'email' => $_POST['email'] ?? null,
                    'telephone' => $_POST['telephone'] ?? null,
                    'fonction' => $_POST['fonction'],
                    'salaire_journalier' => $_POST['salaire_journalier'],
                    'statut' => $_POST['statut']
                ];
                
                if ($this->employeModel->update($id, $data)) {
                    $_SESSION['success'] = "Employé modifié avec succès !";
                    header('Location: index.php?controller=employe&action=view&id=' . $id);
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $employe = $this->employeModel->getById($id);
        
        if (!$employe) {
            $_SESSION['error'] = "Employé non trouvé !";
            header('Location: index.php?controller=employe&action=index');
            exit;
        }
        
        return ['employe' => $employe];
    }
    
    public function view($id) {
        $employe = $this->employeModel->getById($id);
        
        if (!$employe) {
            $_SESSION['error'] = "Employé non trouvé !";
            header('Location: index.php?controller=employe&action=index');
            exit;
        }
        
        // Récupérer les affectations
        $affectations = $this->employeModel->getAffectations($id);
        
        // Récupérer tous les chantiers pour l'affectation
        $chantiers = $this->chantierModel->getAll();
        
        // Gestion de l'affectation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['affecter'])) {
            $chantier_id = $_POST['chantier_id'];
            $date_debut = $_POST['date_debut'];
            $date_fin = $_POST['date_fin'] ?? null;
            
            if ($this->employeModel->affecter($id, $chantier_id, $date_debut, $date_fin)) {
                $_SESSION['success'] = "Employé affecté avec succès !";
                header('Location: index.php?controller=employe&action=view&id=' . $id);
                exit;
            }
        }
        
        return [
            'employe' => $employe,
            'affectations' => $affectations,
            'chantiers' => $chantiers
        ];
    }
    
    public function delete($id) {
        if ($this->auth->hasRole('admin')) {
            try {
                if ($this->employeModel->delete($id)) {
                    $_SESSION['success'] = "Employé supprimé avec succès !";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Action non autorisée !";
        }
        
        header('Location: index.php?controller=employe&action=index');
        exit;
    }
    
    public function terminerAffectation($affectation_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $sql = "UPDATE affectations SET date_fin = :date_fin WHERE id = :id";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([':date_fin' => date('Y-m-d'), ':id' => $affectation_id])) {
                $_SESSION['success'] = "Affectation terminée avec succès !";
            }
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $controller = new EmployeController();
    
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
        case 'terminer_affectation':
            $id = $_GET['id'] ?? 0;
            $controller->terminerAffectation($id);
            break;
        case 'index':
        default:
            $data = $controller->index();
            break;
    }
}
?>