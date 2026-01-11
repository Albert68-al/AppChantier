<?php
// views/dashboard/index.php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Chantier.php';
require_once __DIR__ . '/../../models/Employe.php';

$auth = new Auth();
$auth->requireAuth();

$page_title = "Dashboard";

$chantierModel = new Chantier();
$employeModel = new Employe();

// Récupérer les statistiques
$stats = $chantierModel->getStats();
$employes = $employeModel->getAll();

// Récupérer les chantiers en cours
$chantiersEnCours = $chantierModel->getAll(['statut' => 'en_cours']);

// Calculer les totaux
$totalEmployes = count($employes);
$employesActifs = count(array_filter($employes, function($e) {
    return $e['statut'] == 'actif';
}));

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card en-cours">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Chantiers en cours</h6>
                            <h2 class="mb-0"><?php echo $stats['en_cours'] ?? 0; ?></h2>
                        </div>
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-building text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card termine">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Budget total</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total_budget'] ?? 0, 2, ',', ' '); ?> FBU</h2>
                        </div>
                        <div class="icon-circle bg-success">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card suspendu">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Employés actifs</h6>
                            <h2 class="mb-0"><?php echo $employesActifs; ?></h2>
                        </div>
                        <div class="icon-circle bg-warning">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Total chantiers</h6>
                            <h2 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h2>
                        </div>
                        <div class="icon-circle bg-light">
                            <i class="fas fa-hard-hat text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statut des chantiers</h5>
                </div>
                <div class="card-body">
                    <canvas id="chantierChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Progression des chantiers</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($chantiersEnCours as $chantier): 
                        $depenses = $chantierModel->getDepenses($chantier['id']);
                        $pourcentage = $chantier['budget_total'] > 0 ? 
                            min(($depenses / $chantier['budget_total']) * 100, 100) : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?php echo substr($chantier['nom'], 0, 20); ?>...</span>
                            <span><?php echo number_format($pourcentage, 0); ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar 
                                <?php echo $pourcentage > 80 ? 'bg-danger' : 
                                       ($pourcentage > 60 ? 'bg-warning' : 'bg-success'); ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo $pourcentage; ?>%">
                            </div>
                        </div>
                        <small class="text-muted">
                            <?php echo number_format($depenses, 2, ',', ' '); ?> FBU / 
                            <?php echo number_format($chantier['budget_total'], 2, ',', ' '); ?> FBU
                        </small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Liste des chantiers récents -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Chantiers en cours</h5>
                    <a href="index.php?controller=chantier&action=create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nouveau chantier
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Client</th>
                                    <th>Localisation</th>
                                    <th>Budget</th>
                                    <th>Date début</th>
                                    <th>Chef</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chantiersEnCours as $chantier): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($chantier['nom']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($chantier['client']); ?></td>
                                    <td><?php echo htmlspecialchars($chantier['localisation']); ?></td>
                                    <td><?php echo number_format($chantier['budget_total'], 2, ',', ' '); ?> FBU</td>
                                    <td><?php echo date('d/m/Y', strtotime($chantier['date_debut'])); ?></td>
                                    <td>
                                        <?php if ($chantier['chef_nom']): ?>
                                        <?php echo htmlspecialchars($chantier['chef_prenom'] . ' ' . $chantier['chef_nom']); ?>
                                        <?php else: ?>
                                        <span class="text-muted">Non assigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $chantier['statut'] == 'en_cours' ? 'bg-primary' : 
                                                   ($chantier['statut'] == 'termine' ? 'bg-success' : 'bg-warning'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $chantier['statut'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?controller=chantier&action=view&id=<?php echo $chantier['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'chef'): ?>
                                        <a href="index.php?controller=chantier&action=edit&id=<?php echo $chantier['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique des chantiers
const ctx = document.getElementById('chantierChart').getContext('2d');
const chantierChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['En cours', 'Terminés', 'Suspendus'],
        datasets: [{
            label: 'Nombre de chantiers',
            data: [
                <?php echo $stats['en_cours'] ?? 0; ?>,
                <?php echo $stats['termine'] ?? 0; ?>,
                <?php echo $stats['suspendu'] ?? 0; ?>
            ],
            backgroundColor: [
                'rgba(52, 152, 219, 0.7)',
                'rgba(39, 174, 96, 0.7)',
                'rgba(243, 156, 18, 0.7)'
            ],
            borderColor: [
                'rgb(52, 152, 219)',
                'rgb(39, 174, 96)',
                'rgb(243, 156, 18)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Fonction pour les cercles d'icônes
document.addEventListener('DOMContentLoaded', function() {
    const iconCircles = document.querySelectorAll('.icon-circle');
    iconCircles.forEach(circle => {
        circle.style.width = '50px';
        circle.style.height = '50px';
        circle.style.borderRadius = '50%';
        circle.style.display = 'flex';
        circle.style.alignItems = 'center';
        circle.style.justifyContent = 'center';
        circle.style.fontSize = '1.5rem';
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>