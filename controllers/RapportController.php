<?php
// controllers/RapportController.php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Chantier.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/Finance.php';
require_once __DIR__ . '/../models/Materiau.php';

class RapportController {
    private $chantierModel;
    private $employeModel;
    private $financeModel;
    private $materiauModel;
    private $auth;
    
    public function __construct() {
        $this->chantierModel = new Chantier();
        $this->employeModel = new Employe();
        $this->financeModel = new Finance();
        $this->materiauModel = new Materiau();
        $this->auth = new Auth();
        $this->auth->requireRole('admin'); // Seul l'admin peut générer des rapports
    }
    
    public function index() {
        // Par défaut, rapport du mois en cours
        $date_debut = date('Y-m-01');
        $date_fin = date('Y-m-t');
        
        if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
            $date_debut = $_GET['date_debut'];
        }
        
        if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
            $date_fin = $_GET['date_fin'];
        }
        
        // Récupérer les données
        $statsChantiers = $this->chantierModel->getStats();
        $depenses = $this->financeModel->getDepenses([
            'date_debut' => $date_debut,
            'date_fin' => $date_fin
        ]);
        
        $paiements = $this->financeModel->getPaiements([
            'date_debut' => $date_debut,
            'date_fin' => $date_fin
        ]);
        
        $chantiers = $this->chantierModel->getAll();
        $employes = $this->employeModel->getAll(['statut' => 'actif']);
        $materiaux = $this->materiauModel->getAll();
        
        // Calculer les statistiques
        $totalDepenses = array_sum(array_column($depenses, 'montant'));
        $totalPaiements = array_sum(array_column($paiements, 'montant'));
        
        // Dépenses par catégorie
        $depensesParCategorie = [];
        foreach ($depenses as $depense) {
            $categorie = $depense['type_depense'];
            if (!isset($depensesParCategorie[$categorie])) {
                $depensesParCategorie[$categorie] = 0;
            }
            $depensesParCategorie[$categorie] += $depense['montant'];
        }
        
        // Dépenses par chantier
        $depensesParChantier = [];
        foreach ($depenses as $depense) {
            $chantier_id = $depense['chantier_id'];
            if (!isset($depensesParChantier[$chantier_id])) {
                $depensesParChantier[$chantier_id] = [
                    'nom' => $depense['chantier_nom'] ?? 'Inconnu',
                    'total' => 0
                ];
            }
            $depensesParChantier[$chantier_id]['total'] += $depense['montant'];
        }
        
        // Alertes stock
        $alertesStock = array_filter($materiaux, function($m) {
            return $m['quantite_disponible'] <= $m['seuil_alerte'];
        });
        
        return [
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'statsChantiers' => $statsChantiers,
            'depenses' => $depenses,
            'paiements' => $paiements,
            'chantiers' => $chantiers,
            'employes' => $employes,
            'materiaux' => $materiaux,
            'totalDepenses' => $totalDepenses,
            'totalPaiements' => $totalPaiements,
            'depensesParCategorie' => $depensesParCategorie,
            'depensesParChantier' => $depensesParChantier,
            'alertesStock' => $alertesStock
        ];
    }
    
    public function genererRapportGlobal() {
        $data = $this->index();
        
        // Générer le rapport en PDF (simplifié)
        $html = $this->genererHTMLRapportGlobal($data);
        
        // En production, convertir en PDF
        echo $html;
        exit;
    }
    
    private function genererHTMLRapportGlobal($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Rapport Global - <?php echo date('d/m/Y'); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
                h1, h2, h3 { color: #2c3e50; }
                h1 { border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; background-color: #e8f4f8; }
                .alerte { color: red; font-weight: bold; }
                .section { margin: 30px 0; }
                .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .stat-box { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <div>
                    <h1>Rapport Global d'Activité</h1>
                    <p>Période : <?php echo date('d/m/Y', strtotime($data['date_debut'])); ?> 
                       au <?php echo date('d/m/Y', strtotime($data['date_fin'])); ?></p>
                </div>
                <div>
                    <p>Généré le : <?php echo date('d/m/Y à H:i'); ?></p>
                    <p>© Gestion des Chantiers</p>
                </div>
            </div>
            
            <div class="section">
                <h2>Statistiques Générales</h2>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <div class="stat-box" style="flex: 1;">
                        <h4>Chantiers</h4>
                        <p>Total : <?php echo $data['statsChantiers']['total']; ?></p>
                        <p>En cours : <?php echo $data['statsChantiers']['en_cours']; ?></p>
                        <p>Terminés : <?php echo $data['statsChantiers']['termine']; ?></p>
                    </div>
                    <div class="stat-box" style="flex: 1;">
                        <h4>Finances</h4>
                        <p>Dépenses : <?php echo number_format($data['totalDepenses'], 2, ',', ' '); ?> FBU</p>
                        <p>Paiements : <?php echo number_format($data['totalPaiements'], 2, ',', ' '); ?> FBU</p>
                        <p>Solde : <?php echo number_format($data['totalPaiements'] - $data['totalDepenses'], 2, ',', ' '); ?> FBU</p>
                    </div>
                    <div class="stat-box" style="flex: 1;">
                        <h4>Personnel</h4>
                        <p>Employés actifs : <?php echo count($data['employes']); ?></p>
                        <p>Matériaux : <?php echo count($data['materiaux']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Dépenses par Catégorie</h2>
                <table>
                    <tr>
                        <th>Catégorie</th>
                        <th>Montant</th>
                        <th>Pourcentage</th>
                    </tr>
                    <?php foreach ($data['depensesParCategorie'] as $categorie => $montant): 
                        $pourcentage = $data['totalDepenses'] > 0 ? ($montant / $data['totalDepenses']) * 100 : 0;
                    ?>
                    <tr>
                        <td><?php echo ucfirst($categorie); ?></td>
                        <td><?php echo number_format($montant, 2, ',', ' '); ?> FBU</td>
                        <td><?php echo number_format($pourcentage, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total">
                        <td>Total</td>
                        <td><?php echo number_format($data['totalDepenses'], 2, ',', ' '); ?> FBU</td>
                        <td>100%</td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <h2>Dépenses par Chantier</h2>
                <table>
                    <tr>
                        <th>Chantier</th>
                        <th>Montant Dépensé</th>
                    </tr>
                    <?php foreach ($data['depensesParChantier'] as $chantier): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($chantier['nom']); ?></td>
                        <td><?php echo number_format($chantier['total'], 2, ',', ' '); ?> FBU</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <?php if (!empty($data['alertesStock'])): ?>
            <div class="section">
                <h2 class="alerte">Alertes Stock</h2>
                <table>
                    <tr>
                        <th>Matériau</th>
                        <th>Quantité Disponible</th>
                        <th>Seuil d'Alerte</th>
                        <th>Statut</th>
                    </tr>
                    <?php foreach ($data['alertesStock'] as $materiau): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($materiau['nom']); ?></td>
                        <td><?php echo $materiau['quantite_disponible'] . ' ' . $materiau['unite_mesure']; ?></td>
                        <td><?php echo $materiau['seuil_alerte']; ?></td>
                        <td class="alerte">À réapprovisionner</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="section">
                <h2>Détail des Dépenses</h2>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Chantier</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Montant</th>
                    </tr>
                    <?php foreach ($data['depenses'] as $depense): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($depense['date_depense'])); ?></td>
                        <td><?php echo htmlspecialchars($depense['chantier_nom'] ?? 'N/A'); ?></td>
                        <td><?php echo ucfirst($depense['type_depense']); ?></td>
                        <td><?php echo htmlspecialchars(substr($depense['description'], 0, 50)); ?>...</td>
                        <td><?php echo number_format($depense['montant'], 2, ',', ' '); ?> FBU</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <div class="section">
                <h2>Paiements Reçus</h2>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Chantier</th>
                        <th>Mode</th>
                        <th>Montant</th>
                    </tr>
                    <?php foreach ($data['paiements'] as $paiement): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?></td>
                        <td><?php echo htmlspecialchars($paiement['chantier_nom'] ?? 'N/A'); ?></td>
                        <td><?php echo ucfirst($paiement['mode_paiement'] ?? 'N/A'); ?></td>
                        <td><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> FBU</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <div style="margin-top: 50px; text-align: center; font-size: 0.9em; color: #666; border-top: 1px solid #ddd; padding-top: 20px;">
                <p>Fin du rapport - Document confidentiel</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    public function exportExcel() {
        $data = $this->index();
        
        // En-tête pour Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="rapport_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "<table border='1'>";
        echo "<tr><th colspan='5'>Rapport Global - " . date('d/m/Y') . "</th></tr>";
        echo "<tr><th>Catégorie</th><th>Donnée</th><th>Valeur</th></tr>";
        
        // Statistiques
        echo "<tr><td colspan='3'><strong>STATISTIQUES</strong></td></tr>";
        echo "<tr><td>Chantiers totaux</td><td></td><td>" . $data['statsChantiers']['total'] . "</td></tr>";
        echo "<tr><td>Chantiers en cours</td><td></td><td>" . $data['statsChantiers']['en_cours'] . "</td></tr>";
        echo "<tr><td>Chantiers terminés</td><td></td><td>" . $data['statsChantiers']['termine'] . "</td></tr>";
        echo "<tr><td>Total dépenses</td><td></td><td>" . number_format($data['totalDepenses'], 2, ',', ' ') . " FBU</td></tr>";
        echo "<tr><td>Total paiements</td><td></td><td>" . number_format($data['totalPaiements'], 2, ',', ' ') . " FBU</td></tr>";
        
        // Dépenses par catégorie
        echo "<tr><td colspan='3'><strong>DÉPENSES PAR CATÉGORIE</strong></td></tr>";
        foreach ($data['depensesParCategorie'] as $categorie => $montant) {
            echo "<tr><td>" . ucfirst($categorie) . "</td><td></td><td>" . number_format($montant, 2, ',', ' ') . " FBU</td></tr>";
        }
        
        echo "</table>";
        exit;
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $controller = new RapportController();
    
    switch ($_GET['action']) {
        case 'generer_global':
            $controller->genererRapportGlobal();
            break;
        case 'export_excel':
            $controller->exportExcel();
            break;
        case 'index':
        default:
            $data = $controller->index();
            break;
    }
}
?>