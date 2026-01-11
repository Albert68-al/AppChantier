<?php
// controllers/FinanceController.php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Chantier.php';
require_once __DIR__ . '/../models/Finance.php';

class FinanceController {
    private $chantierModel;
    private $financeModel;
    private $auth;
    
    public function __construct() {
        $this->chantierModel = new Chantier();
        $this->financeModel = new Finance();
        $this->auth = new Auth();
        $this->auth->requireAuth();
        
        // Seuls admin et comptable peuvent accéder
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('comptable')) {
            $_SESSION['error'] = "Accès refusé !";
            header('Location: ../views/dashboard/index.php');
            exit;
        }
    }
    
    public function index() {
        $filters = [];
        
        if (isset($_GET['chantier_id']) && !empty($_GET['chantier_id'])) {
            $filters['chantier_id'] = $_GET['chantier_id'];
        }
        
        if (isset($_GET['type_depense']) && !empty($_GET['type_depense'])) {
            $filters['type_depense'] = $_GET['type_depense'];
        }
        
        if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
            $filters['date_debut'] = $_GET['date_debut'];
        }
        
        if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
            $filters['date_fin'] = $_GET['date_fin'];
        }
        
        $depenses = $this->financeModel->getDepenses($filters);
        $paiements = $this->financeModel->getPaiements($filters);
        $chantiers = $this->chantierModel->getAll();
        
        // Calculer les totaux
        $totalDepenses = array_sum(array_column($depenses, 'montant'));
        $totalPaiements = array_sum(array_column($paiements, 'montant'));
        $soldeTotal = $totalPaiements - $totalDepenses;
        
        return [
            'depenses' => $depenses,
            'paiements' => $paiements,
            'chantiers' => $chantiers,
            'filters' => $filters,
            'totalDepenses' => $totalDepenses,
            'totalPaiements' => $totalPaiements,
            'soldeTotal' => $soldeTotal
        ];
    }
    
    public function ajouter_depense() {
        return $this->ajouterDepense();
    }
    
    public function ajouterDepense() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'chantier_id' => $_POST['chantier_id'],
                    'type_depense' => $_POST['type_depense'],
                    'description' => $_POST['description'],
                    'montant' => $_POST['montant'],
                    'date_depense' => $_POST['date_depense'],
                    'justificatif' => $_POST['justificatif'] ?? null
                ];
                
                if ($this->financeModel->ajouterDepense($data)) {
                    $_SESSION['success'] = "Dépense ajoutée avec succès !";
                    header('Location: index.php?controller=finance&action=index');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $chantiers = $this->chantierModel->getAll();
        
        return ['chantiers' => $chantiers];
    }
    
    public function ajouter_paiement() {
        return $this->ajouterPaiement();
    }
    
    public function ajouterPaiement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'chantier_id' => $_POST['chantier_id'],
                    'montant' => $_POST['montant'],
                    'date_paiement' => $_POST['date_paiement'],
                    'mode_paiement' => $_POST['mode_paiement'],
                    'reference' => $_POST['reference'] ?? null,
                    'notes' => $_POST['notes'] ?? null
                ];
                
                if ($this->financeModel->ajouterPaiement($data)) {
                    $_SESSION['success'] = "Paiement enregistré avec succès !";
                    header('Location: index.php?controller=finance&action=index');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $chantiers = $this->chantierModel->getAll();
        
        return ['chantiers' => $chantiers];
    }
    
    public function delete_depense($id) {
        return $this->deleteDepense($id);
    }
    
    public function deleteDepense($id) {
        if ($this->auth->hasRole('admin')) {
            try {
                if ($this->financeModel->deleteDepense($id)) {
                    $_SESSION['success'] = "Dépense supprimée avec succès !";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Action non autorisée !";
        }
        
        header('Location: index.php?controller=finance&action=index');
        exit;
    }
    
    public function delete_paiement($id) {
        return $this->deletePaiement($id);
    }
    
    public function deletePaiement($id) {
        if ($this->auth->hasRole('admin')) {
            try {
                if ($this->financeModel->deletePaiement($id)) {
                    $_SESSION['success'] = "Paiement supprimé avec succès !";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Action non autorisée !";
        }
        
        header('Location: index.php?controller=finance&action=index');
        exit;
    }
    
    public function rapport_chantier($chantier_id) {
        return $this->rapportChantier($chantier_id);
    }
    
    public function rapportChantier($chantier_id) {
        $chantier = $this->chantierModel->getById($chantier_id);
        
        if (!$chantier) {
            $_SESSION['error'] = "Chantier non trouvé !";
            header('Location: index.php?controller=finance&action=index');
            exit;
        }
        
        $depenses = $this->financeModel->getDepenses(['chantier_id' => $chantier_id]);
        $paiements = $this->financeModel->getPaiements(['chantier_id' => $chantier_id]);
        
        // Calculer les totaux par catégorie
        $categoriesDepenses = [];
        foreach ($depenses as $depense) {
            $type = $depense['type_depense'];
            if (!isset($categoriesDepenses[$type])) {
                $categoriesDepenses[$type] = 0;
            }
            $categoriesDepenses[$type] += $depense['montant'];
        }
        
        $totalDepenses = array_sum(array_column($depenses, 'montant'));
        $totalPaiements = array_sum(array_column($paiements, 'montant'));
        $solde = $chantier['budget_total'] - $totalDepenses;
        
        return [
            'chantier' => $chantier,
            'depenses' => $depenses,
            'paiements' => $paiements,
            'categoriesDepenses' => $categoriesDepenses,
            'totalDepenses' => $totalDepenses,
            'totalPaiements' => $totalPaiements,
            'solde' => $solde,
            'budgetTotal' => $chantier['budget_total']
        ];
    }
    
    public function generer_pdf($chantier_id) {
        return $this->genererRapportPDF($chantier_id);
    }
    
    public function genererRapportPDF($chantier_id) {
        $data = $this->rapportChantier($chantier_id);
        
        // Générer le PDF (simplifié - en réalité utiliser une librairie comme TCPDF ou Dompdf)
        $html = $this->genererHTMLRapport($data);
        
        // Pour l'instant, on affiche juste l'HTML
        // En production, on utiliserait une librairie PDF
        echo $html;
        exit;
    }
    
    private function genererHTMLRapport($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Rapport Financier - <?php echo htmlspecialchars($data['chantier']['nom']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                h2 { color: #34495e; margin-top: 30px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; background-color: #e8f4f8; }
                .positif { color: green; }
                .negatif { color: red; }
                .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
                .info-box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <div>
                    <h1>Rapport Financier</h1>
                    <h2>Chantier : <?php echo htmlspecialchars($data['chantier']['nom']); ?></h2>
                    <p>Client : <?php echo htmlspecialchars($data['chantier']['client']); ?></p>
                    <p>Localisation : <?php echo htmlspecialchars($data['chantier']['localisation']); ?></p>
                </div>
                <div>
                    <p>Date du rapport : <?php echo date('d/m/Y'); ?></p>
                    <p>Statut : <?php echo ucfirst(str_replace('_', ' ', $data['chantier']['statut'])); ?></p>
                </div>
            </div>
            
            <div class="info-box">
                <h3>Résumé Financier</h3>
                <p>Budget total : <strong><?php echo number_format($data['budgetTotal'], 2, ',', ' '); ?> €</strong></p>
                <p>Total dépenses : <strong><?php echo number_format($data['totalDepenses'], 2, ',', ' '); ?> €</strong></p>
                <p>Total paiements reçus : <strong><?php echo number_format($data['totalPaiements'], 2, ',', ' '); ?> €</strong></p>
                <p>Solde restant : <strong class="<?php echo $data['solde'] >= 0 ? 'positif' : 'negatif'; ?>">
                    <?php echo number_format($data['solde'], 2, ',', ' '); ?> €
                </strong></p>
                <p>Taux d'utilisation du budget : <strong>
                    <?php echo $data['budgetTotal'] > 0 ? number_format(($data['totalDepenses'] / $data['budgetTotal']) * 100, 1) : 0; ?>%
                </strong></p>
            </div>
            
            <h2>Dépenses par catégorie</h2>
            <table>
                <tr>
                    <th>Catégorie</th>
                    <th>Montant</th>
                    <th>Pourcentage</th>
                </tr>
                <?php foreach ($data['categoriesDepenses'] as $categorie => $montant): 
                    $pourcentage = $data['totalDepenses'] > 0 ? ($montant / $data['totalDepenses']) * 100 : 0;
                ?>
                <tr>
                    <td><?php echo ucfirst($categorie); ?></td>
                    <td><?php echo number_format($montant, 2, ',', ' '); ?> €</td>
                    <td><?php echo number_format($pourcentage, 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
                <tr class="total">
                    <td>Total</td>
                    <td><?php echo number_format($data['totalDepenses'], 2, ',', ' '); ?> €</td>
                    <td>100%</td>
                </tr>
            </table>
            
            <h2>Détail des dépenses</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Montant</th>
                </tr>
                <?php foreach ($data['depenses'] as $depense): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($depense['date_depense'])); ?></td>
                    <td><?php echo ucfirst($depense['type_depense']); ?></td>
                    <td><?php echo htmlspecialchars($depense['description']); ?></td>
                    <td><?php echo number_format($depense['montant'], 2, ',', ' '); ?> €</td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <h2>Paiements reçus</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Mode de paiement</th>
                    <th>Référence</th>
                    <th>Montant</th>
                </tr>
                <?php foreach ($data['paiements'] as $paiement): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?></td>
                    <td><?php echo ucfirst($paiement['mode_paiement'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($paiement['reference'] ?? ''); ?></td>
                    <td><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €</td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <div style="margin-top: 50px; text-align: center; font-size: 0.9em; color: #666;">
                <p>Document généré le <?php echo date('d/m/Y à H:i'); ?></p>
                <p>© <?php echo date('Y'); ?> - Gestion des Chantiers</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $controller = new FinanceController();
    
    switch ($_GET['action']) {
        case 'ajouter_depense':
            $data = $controller->ajouterDepense();
            break;
        case 'ajouter_paiement':
            $data = $controller->ajouterPaiement();
            break;
        case 'delete_depense':
            $id = $_GET['id'] ?? 0;
            $controller->deleteDepense($id);
            break;
        case 'delete_paiement':
            $id = $_GET['id'] ?? 0;
            $controller->deletePaiement($id);
            break;
        case 'rapport_chantier':
            $id = $_GET['id'] ?? 0;
            $data = $controller->rapportChantier($id);
            break;
        case 'generer_pdf':
            $id = $_GET['id'] ?? 0;
            $controller->genererRapportPDF($id);
            break;
        case 'index':
        default:
            $data = $controller->index();
            break;
    }
}
?>