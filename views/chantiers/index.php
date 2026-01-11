<?php
// views/chantiers/index.php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Chantier.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Chantiers";

$chantierModel = new Chantier();

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$chantiers = $data['chantiers'] ?? [];
$chefs = $data['chefs'] ?? [];
$filters = $data['filters'] ?? [];
?>

<div class="container-fluid">
    <!-- En-tête avec boutons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Gestion des Chantiers</h4>
        <div>
            <a href="index.php?controller=chantier&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau chantier
            </a>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="controller" value="chantier">
                <input type="hidden" name="action" value="index">
                
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_cours" <?php echo ($filters['statut'] ?? '') == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="termine" <?php echo ($filters['statut'] ?? '') == 'termine' ? 'selected' : ''; ?>>Terminé</option>
                        <option value="suspendu" <?php echo ($filters['statut'] ?? '') == 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <input type="text" name="client" class="form-control" 
                           placeholder="Rechercher par client" 
                           value="<?php echo htmlspecialchars($filters['client'] ?? ''); ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="index.php?controller=chantier&action=index" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tableau des chantiers -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Liste des chantiers</h5>
        </div>
        <div class="card-body">
            <?php if (empty($chantiers)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun chantier trouvé.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="tableChantiers">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Client</th>
                            <th>Localisation</th>
                            <th>Budget</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Chef</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chantiers as $chantier): 
                            $depenses = $chantierModel->getDepenses($chantier['id']);
                            $pourcentage = $chantier['budget_total'] > 0 ? 
                                min(($depenses / $chantier['budget_total']) * 100, 100) : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($chantier['nom']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <div class="progress" style="height: 5px; width: 100px;">
                                        <div class="progress-bar 
                                            <?php echo $pourcentage > 80 ? 'bg-danger' : 
                                                   ($pourcentage > 60 ? 'bg-warning' : 'bg-success'); ?>" 
                                             style="width: <?php echo $pourcentage; ?>%">
                                        </div>
                                    </div>
                                    <?php echo number_format($pourcentage, 0); ?>%
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars($chantier['client']); ?></td>
                            <td><?php echo htmlspecialchars($chantier['localisation']); ?></td>
                            <td><?php echo number_format($chantier['budget_total'], 2, ',', ' '); ?> FBU</td>
                            <td><?php echo date('d/m/Y', strtotime($chantier['date_debut'])); ?></td>
                            <td>
                                <?php echo $chantier['date_fin'] ? 
                                    date('d/m/Y', strtotime($chantier['date_fin'])) : 
                                    '<span class="text-muted">En cours</span>'; ?>
                            </td>
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
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="index.php?controller=chantier&action=view&id=<?php echo $chantier['id']; ?>" 
                                       class="btn btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'chef'): ?>
                                    <a href="index.php?controller=chantier&action=edit&id=<?php echo $chantier['id']; ?>" 
                                       class="btn btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="index.php?controller=chantier&action=delete&id=<?php echo $chantier['id']; ?>" 
                                       class="btn btn-outline-danger btn-delete" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
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

<script>
// Script pour la recherche en temps réel
document.addEventListener('DOMContentLoaded', function() {
    // Recherche dans le tableau
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control mb-3';
    searchInput.placeholder = 'Rechercher dans le tableau...';
    
    const cardHeader = document.querySelector('.card-header');
    if (cardHeader) {
        cardHeader.appendChild(searchInput);
        
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tableChantiers tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>