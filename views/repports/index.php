<?php
// views/rapports/index.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireRole('admin');

$page_title = "Rapports";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$date_debut = $data['date_debut'] ?? date('Y-m-01');
$date_fin = $data['date_fin'] ?? date('Y-m-t');
$statsChantiers = $data['statsChantiers'] ?? [];
$totalDepenses = $data['totalDepenses'] ?? 0;
$totalPaiements = $data['totalPaiements'] ?? 0;
$depensesParCategorie = $data['depensesParCategorie'] ?? [];
$depensesParChantier = $data['depensesParChantier'] ?? [];
$alertesStock = $data['alertesStock'] ?? [];
?>

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Rapports et Statistiques</h4>
        <div>
            <button onclick="window.print()" class="btn btn-secondary me-2">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <a href="index.php?controller=rapport&action=export_excel" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exporter Excel
            </a>
        </div>
    </div>
    
    <!-- Filtres période -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="controller" value="rapport">
                <input type="hidden" name="action" value="index">
                
                <div class="col-md-3">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_debut" class="form-control" value="<?php echo $date_debut; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="<?php echo $date_fin; ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sync"></i> Actualiser
                    </button>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Rapports prédéfinis</label>
                    <div class="btn-group w-100" role="group">
                        <a href="index.php?controller=rapport&action=index&date_debut=<?php echo date('Y-m-01'); ?>&date_fin=<?php echo date('Y-m-t'); ?>" 
                           class="btn btn-outline-secondary">
                            Ce mois
                        </a>
                        <a href="index.php?controller=rapport&action=index&date_debut=<?php echo date('Y-01-01'); ?>&date_fin=<?php echo date('Y-12-31'); ?>" 
                           class="btn btn-outline-secondary">
                            Cette année
                        </a>
                        <a href="index.php?controller=rapport&action=generer_global" 
                           class="btn btn-outline-primary" target="_blank">
                            PDF Global
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Période sélectionnée -->
    <div class="alert alert-info mb-4">
        <h5><i class="fas fa-calendar-alt"></i> Période : 
            <?php echo date('d/m/Y', strtotime($date_debut)); ?> 
            au <?php echo date('d/m/Y', strtotime($date_fin)); ?>
        </h5>
    </div>
    
    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Chantiers totaux</h6>
                    <h1><?php echo $statsChantiers['total'] ?? 0; ?></h1>
                    <small><?php echo ($statsChantiers['en_cours'] ?? 0); ?> en cours</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6>Dépenses</h6>
                    <h1><?php echo number_format($totalDepenses, 0, ',', ' '); ?> FBU</h1>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Paiements</h6>
                    <h1><?php echo number_format($totalPaiements, 0, ',', ' '); ?> FBU</h1>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card 
                <?php echo ($totalPaiements - $totalDepenses) >= 0 ? 'bg-info' : 'bg-danger'; ?> text-white">
                <div class="card-body text-center">
                    <h6>Bénéfice/Perte</h6>
                    <h1><?php echo number_format($totalPaiements - $totalDepenses, 0, ',', ' '); ?> FBU</h1>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dépenses par Catégorie</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartCategories" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dépenses par Chantier</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartChantiers" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Détails -->
    <div class="row">
        <div class="col-md-6">
            <!-- Dépenses par catégorie détaillé -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Détail des dépenses par catégorie</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Montant</th>
                                    <th>Pourcentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($depensesParCategorie as $categorie => $montant): 
                                    $pourcentage = $totalDepenses > 0 ? ($montant / $totalDepenses) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo ucfirst($categorie); ?></td>
                                    <td><?php echo number_format($montant, 2, ',', ' '); ?> FBU</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                                <div class="progress-bar" style="width: <?php echo $pourcentage; ?>%"></div>
                                            </div>
                                            <span><?php echo number_format($pourcentage, 1); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- Alertes stock -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Alertes Stock</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($alertesStock)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Aucune alerte stock.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Matériau</th>
                                    <th>Stock actuel</th>
                                    <th>Seuil alerte</th>
                                    <th>Unité</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alertesStock as $materiau): ?>
                                <tr class="table-warning">
                                    <td><?php echo htmlspecialchars($materiau['nom']); ?></td>
                                    <td class="text-danger fw-bold">
                                        <?php echo $materiau['quantite_disponible']; ?>
                                    </td>
                                    <td><?php echo $materiau['seuil_alerte']; ?></td>
                                    <td><?php echo htmlspecialchars($materiau['unite_mesure']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour les graphiques
    const categoriesData = {
        labels: [<?php echo implode(', ', array_map(function($cat) { return "'" . ucfirst($cat) . "'"; }, array_keys($depensesParCategorie))); ?>],
        datasets: [{
            data: [<?php echo implode(', ', array_values($depensesParCategorie)); ?>],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#8AC926', '#1982C4'
            ]
        }]
    };
    
    const chantiersData = {
        labels: [<?php echo implode(', ', array_map(function($chantier) { return "'" . htmlspecialchars($chantier['nom']) . "'"; }, array_values($depensesParChantier))); ?>],
        datasets: [{
            label: 'Dépenses (FBU)',
            data: [<?php echo implode(', ', array_map(function($chantier) { return $chantier['total']; }, array_values($depensesParChantier))); ?>],
            backgroundColor: '#36A2EB'
        }]
    };
    
    // Graphique des catégories
    const ctx1 = document.getElementById('chartCategories').getContext('2d');
    new Chart(ctx1, {
        type: 'pie',
        data: categoriesData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Graphique des chantiers
    const ctx2 = document.getElementById('chartChantiers').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: chantiersData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' FBU';
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>