<?php
// views/finances/index.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

// Vérifier le rôle
if (!$auth->hasRole('admin') && !$auth->hasRole('comptable')) {
    $_SESSION['error'] = "Accès refusé !";
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$page_title = "Finances";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$depenses = $data['depenses'] ?? [];
$paiements = $data['paiements'] ?? [];
$chantiers = $data['chantiers'] ?? [];
$filters = $data['filters'] ?? [];
$totalDepenses = $data['totalDepenses'] ?? 0;
$totalPaiements = $data['totalPaiements'] ?? 0;
$soldeTotal = $data['soldeTotal'] ?? 0;
?>

<div class="container-fluid">
    <!-- En-tête avec statistiques -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Gestion Financière</h4>
        <div>
            <a href="index.php?controller=finance&action=ajouter_depense" class="btn btn-warning me-2">
                <i class="fas fa-money-bill-wave"></i> Ajouter dépense
            </a>
            <a href="index.php?controller=finance&action=ajouter_paiement" class="btn btn-success">
                <i class="fas fa-hand-holding-usd"></i> Enregistrer paiement
            </a>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Dépenses</h6>
                    <h3><?php echo number_format($totalDepenses, 2, ',', ' '); ?> FBU</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Paiements</h6>
                    <h3><?php echo number_format($totalPaiements, 2, ',', ' '); ?> FBU</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card 
                <?php echo $soldeTotal >= 0 ? 'bg-info' : 'bg-danger'; ?> text-white">
                <div class="card-body">
                    <h6 class="card-title">Solde Global</h6>
                    <h3><?php echo number_format($soldeTotal, 2, ',', ' '); ?> FBU</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="controller" value="finance">
                <input type="hidden" name="action" value="index">
                
                <div class="col-md-3">
                    <label class="form-label">Chantier</label>
                    <select name="chantier_id" class="form-select">
                        <option value="">Tous les chantiers</option>
                        <?php foreach ($chantiers as $chantier): ?>
                        <option value="<?php echo $chantier['id']; ?>" 
                            <?php echo ($filters['chantier_id'] ?? '') == $chantier['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($chantier['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Type de dépense</label>
                    <select name="type_depense" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="materiel" <?php echo ($filters['type_depense'] ?? '') == 'materiel' ? 'selected' : ''; ?>>Matériel</option>
                        <option value="salaire" <?php echo ($filters['type_depense'] ?? '') == 'salaire' ? 'selected' : ''; ?>>Salaire</option>
                        <option value="transport" <?php echo ($filters['type_depense'] ?? '') == 'transport' ? 'selected' : ''; ?>>Transport</option>
                        <option value="autre" <?php echo ($filters['type_depense'] ?? '') == 'autre' ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_debut" class="form-control" 
                           value="<?php echo $filters['date_debut'] ?? ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_fin" class="form-control" 
                           value="<?php echo $filters['date_fin'] ?? ''; ?>">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-secondary me-2">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                    <a href="index.php?controller=finance&action=index" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Onglets -->
    <ul class="nav nav-tabs mb-4" id="financeTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="depenses-tab" data-bs-toggle="tab" 
                    data-bs-target="#depenses" type="button" role="tab">
                <i class="fas fa-money-bill-wave"></i> Dépenses
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="paiements-tab" data-bs-toggle="tab" 
                    data-bs-target="#paiements" type="button" role="tab">
                <i class="fas fa-hand-holding-usd"></i> Paiements
            </button>
        </li>
    </ul>
    
    <!-- Contenu des onglets -->
    <div class="tab-content" id="financeTabContent">
        <!-- Onglet Dépenses -->
        <div class="tab-pane fade show active" id="depenses" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des dépenses</h5>
                    <span class="badge bg-warning">
                        <?php echo count($depenses); ?> dépenses - 
                        <?php echo number_format($totalDepenses, 2, ',', ' '); ?> FBU
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($depenses)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucune dépense trouvée.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Chantier</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Montant</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($depenses as $depense): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($depense['date_depense'])); ?></td>
                                    <td>
                                        <a href="index.php?controller=chantier&action=view&id=<?php echo $depense['chantier_id']; ?>">
                                            <?php echo htmlspecialchars($depense['chantier_nom']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($depense['type_depense']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($depense['description'], 0, 50)); ?>
                                        <?php if (strlen($depense['description']) > 50): ?>...<?php endif; ?>
                                    </td>
                                    <td class="text-danger fw-bold">
                                        -<?php echo number_format($depense['montant'], 2, ',', ' '); ?> FBU
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['role'] == 'admin'): ?>
                                        <a href="index.php?controller=finance&action=delete_depense&id=<?php echo $depense['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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
        
        <!-- Onglet Paiements -->
        <div class="tab-pane fade" id="paiements" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des paiements</h5>
                    <span class="badge bg-success">
                        <?php echo count($paiements); ?> paiements - 
                        <?php echo number_format($totalPaiements, 2, ',', ' '); ?> FBU
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($paiements)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucun paiement trouvé.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Chantier</th>
                                    <th>Mode</th>
                                    <th>Référence</th>
                                    <th>Montant</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paiements as $paiement): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?></td>
                                    <td>
                                        <a href="index.php?controller=chantier&action=view&id=<?php echo $paiement['chantier_id']; ?>">
                                            <?php echo htmlspecialchars($paiement['chantier_nom']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst($paiement['mode_paiement'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($paiement['reference'] ?? ''); ?></td>
                                    <td class="text-success fw-bold">
                                        +<?php echo number_format($paiement['montant'], 2, ',', ' '); ?> FBU
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['role'] == 'admin'): ?>
                                        <a href="index.php?controller=finance&action=delete_paiement&id=<?php echo $paiement['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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

<script>
// Initialiser les onglets
document.addEventListener('DOMContentLoaded', function() {
    const triggerTabList = [].slice.call(document.querySelectorAll('#financeTab button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>