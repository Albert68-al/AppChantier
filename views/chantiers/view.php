<?php
// views/chantiers/view.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Détail chantier";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$chantier = $data['chantier'] ?? null;
$employes = $data['employes'] ?? [];
$depenses = $data['depenses'] ?? 0;
$paiements = $data['paiements'] ?? 0;
$solde = $data['solde'] ?? 0;
$pourcentage_utilisation = $data['pourcentage_utilisation'] ?? 0;

if (!$chantier) {
    echo '<div class="alert alert-danger">Chantier non trouvé</div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <!-- En-tête du chantier -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4><?php echo htmlspecialchars($chantier['nom']); ?></h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($chantier['localisation']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-user-tie"></i> Client : <?php echo htmlspecialchars($chantier['client']); ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge 
                                <?php echo $chantier['statut'] == 'en_cours' ? 'bg-primary' : 
                                       ($chantier['statut'] == 'termine' ? 'bg-success' : 'bg-warning'); ?> fs-6">
                                <?php echo ucfirst(str_replace('_', ' ', $chantier['statut'])); ?>
                            </span>
                            <div class="mt-2">
                                <a href="index.php?controller=chantier&action=edit&id=<?php echo $chantier['id']; ?>" 
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="index.php?controller=finance&action=rapport_chantier&id=<?php echo $chantier['id']; ?>" 
                                   class="btn btn-sm btn-outline-info" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Rapport
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistiques financières -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Budget total</h6>
                    <h3><?php echo number_format($chantier['budget_total'], 2, ',', ' '); ?> FBU</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Dépenses</h6>
                    <h3><?php echo number_format($depenses, 2, ',', ' '); ?> FBU</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Paiements reçus</h6>
                    <h3><?php echo number_format($paiements, 2, ',', ' '); ?> FBU</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card 
                <?php echo $solde >= 0 ? 'bg-info' : 'bg-danger'; ?> text-white">
                <div class="card-body">
                    <h6 class="card-title">Solde</h6>
                    <h3><?php echo number_format($solde, 2, ',', ' '); ?> FBU</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Barre de progression -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Utilisation du budget</span>
                        <span><?php echo number_format($pourcentage_utilisation, 1); ?>%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar 
                            <?php echo $pourcentage_utilisation > 80 ? 'bg-danger' : 
                                   ($pourcentage_utilisation > 60 ? 'bg-warning' : 'bg-success'); ?>" 
                             role="progressbar" 
                             style="width: <?php echo min($pourcentage_utilisation, 100); ?>%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informations détaillées -->
    <div class="row">
        <div class="col-md-6">
            <!-- Informations générales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td width="40%"><strong>Date de début</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($chantier['date_debut'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Date de fin</strong></td>
                            <td>
                                <?php echo $chantier['date_fin'] ? 
                                    date('d/m/Y', strtotime($chantier['date_fin'])) : 
                                    '<span class="text-muted">Non définie</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Chef de chantier</strong></td>
                            <td>
                                <?php if ($chantier['chef_nom']): ?>
                                <?php echo htmlspecialchars($chantier['chef_prenom'] . ' ' . $chantier['chef_nom']); ?>
                                <?php else: ?>
                                <span class="text-muted">Non assigné</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Description</strong></td>
                            <td>
                                <?php echo $chantier['description'] ? 
                                    nl2br(htmlspecialchars($chantier['description'])) : 
                                    '<span class="text-muted">Aucune description</span>'; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="index.php?controller=finance&action=ajouter_depense" 
                               class="btn btn-outline-warning w-100">
                                <i class="fas fa-money-bill-wave"></i> Ajouter dépense
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="index.php?controller=finance&action=ajouter_paiement" 
                               class="btn btn-outline-success w-100">
                                <i class="fas fa-hand-holding-usd"></i> Enregistrer paiement
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="index.php?controller=materiau&action=consommer" 
                               class="btn btn-outline-primary w-100">
                                <i class="fas fa-tools"></i> Consommer matériel
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="index.php?controller=employe&action=index" 
                               class="btn btn-outline-info w-100">
                                <i class="fas fa-users"></i> Affecter employé
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- Employés affectés -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Employés affectés</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($employes)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucun employé n'est affecté à ce chantier.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Fonction</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employes as $employe): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?controller=employe&action=view&id=<?php echo $employe['id']; ?>">
                                            <?php echo htmlspecialchars($employe['prenom'] . ' ' . $employe['nom']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($employe['fonction']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($employe['date_debut'])); ?></td>
                                    <td>
                                        <?php echo $employe['date_fin'] ? 
                                            date('d/m/Y', strtotime($employe['date_fin'])) : 
                                            '<span class="text-success">En cours</span>'; ?>
                                    </td>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>