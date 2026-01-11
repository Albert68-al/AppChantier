<?php
// controllers/MateriauController.php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Materiau.php';
require_once __DIR__ . '/../models/Chantier.php';

class MateriauController {
    private $materiauModel;
    private $chantierModel;
    private $auth;
    
    public function __construct() {
        $this->materiauModel = new Materiau();
        $this->chantierModel = new Chantier();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }
    
    public function index() {
        $filters = [];
        
        if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
            $filters['categorie'] = $_GET['categorie'];
        }
        
        $materiaux = $this->materiauModel->getAll($filters);
        
        // Vérifier les seuils d'alerte
        $alertes = [];
        foreach ($materiaux as $materiau) {
            if ($materiau['quantite_disponible'] <= $materiau['seuil_alerte']) {
                $alertes[] = $materiau;
            }
        }
        
        return [
            'materiaux' => $materiaux,
            'alertes' => $alertes,
            'filters' => $filters
        ];
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'reference' => $_POST['reference'],
                    'nom' => $_POST['nom'],
                    'description' => $_POST['description'] ?? null,
                    'categorie' => $_POST['categorie'],
                    'unite_mesure' => $_POST['unite_mesure'],
                    'quantite_disponible' => $_POST['quantite_disponible'],
                    'seuil_alerte' => $_POST['seuil_alerte'],
                    'prix_unitaire' => $_POST['prix_unitaire'],
                    'fournisseur' => $_POST['fournisseur'] ?? null
                ];
                
                if ($this->materiauModel->create($data)) {
                    $_SESSION['success'] = "Matériau créé avec succès !";
                    header('Location: index.php?controller=materiau&action=index');
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
                    'reference' => $_POST['reference'],
                    'nom' => $_POST['nom'],
                    'description' => $_POST['description'] ?? null,
                    'categorie' => $_POST['categorie'],
                    'unite_mesure' => $_POST['unite_mesure'],
                    'quantite_disponible' => $_POST['quantite_disponible'],
                    'seuil_alerte' => $_POST['seuil_alerte'],
                    'prix_unitaire' => $_POST['prix_unitaire'],
                    'fournisseur' => $_POST['fournisseur'] ?? null
                ];
                
                if ($this->materiauModel->update($id, $data)) {
                    $_SESSION['success'] = "Matériau modifié avec succès !";
                    header('Location: index.php?controller=materiau&action=index');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $materiau = $this->materiauModel->getById($id);
        
        if (!$materiau) {
            $_SESSION['error'] = "Matériau non trouvé !";
            header('Location: index.php?controller=materiau&action=index');
            exit;
        }
        
        return ['materiau' => $materiau];
    }
    
    public function delete($id) {
        if ($this->auth->hasRole('admin') || $this->auth->hasRole('comptable')) {
            try {
                if ($this->materiauModel->delete($id)) {
                    $_SESSION['success'] = "Matériau supprimé avec succès !";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Action non autorisée !";
        }
        
        header('Location: index.php?controller=materiau&action=index');
        exit;
    }
    
    public function consommer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = Database::getInstance()->getConnection();
                
                $sql = "INSERT INTO consommations (chantier_id, materiau_id, quantite, date_consommation, notes) 
                        VALUES (:chantier_id, :materiau_id, :quantite, :date_consommation, :notes)";
                
                $stmt = $db->prepare($sql);
                
                $success = $stmt->execute([
                    ':chantier_id' => $_POST['chantier_id'],
                    ':materiau_id' => $_POST['materiau_id'],
                    ':quantite' => $_POST['quantite'],
                    ':date_consommation' => $_POST['date_consommation'],
                    ':notes' => $_POST['notes'] ?? null
                ]);
                
                // Mettre à jour le stock
                if ($success) {
                    $updateSql = "UPDATE materiaux 
                                  SET quantite_disponible = quantite_disponible - :quantite 
                                  WHERE id = :id";
                    
                    $updateStmt = $db->prepare($updateSql);
                    $updateStmt->execute([
                        ':quantite' => $_POST['quantite'],
                        ':id' => $_POST['materiau_id']
                    ]);
                    
                    $_SESSION['success'] = "Consommation enregistrée avec succès !";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
            
            header('Location: index.php?controller=materiau&action=index');
            exit;
        }
        
        $materiaux = $this->materiauModel->getAll();
        $chantiers = $this->chantierModel->getAll();
        
        return [
            'materiaux' => $materiaux,
            'chantiers' => $chantiers
        ];
    }
    
    public function getConsommations($chantier_id = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT c.*, m.nom as materiau_nom, m.reference, ch.nom as chantier_nom 
                FROM consommations c 
                JOIN materiaux m ON c.materiau_id = m.id 
                JOIN chantiers ch ON c.chantier_id = ch.id";
        
        $params = [];
        
        if ($chantier_id) {
            $sql .= " WHERE c.chantier_id = :chantier_id";
            $params[':chantier_id'] = $chantier_id;
        }
        
        $sql .= " ORDER BY c.date_consommation DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $controller = new MateriauController();
    
    switch ($_GET['action']) {
        case 'create':
            $data = $controller->create();
            break;
        case 'edit':
            $id = $_GET['id'] ?? 0;
            $data = $controller->edit($id);
            break;
        case 'delete':
            $id = $_GET['id'] ?? 0;
            $controller->delete($id);
            break;
        case 'consommer':
            $data = $controller->consommer();
            break;
        case 'index':
        default:
            $data = $controller->index();
            break;
    }
}
?>