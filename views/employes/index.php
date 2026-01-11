<?php
// views/employes/index.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Employés";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$employes = $data['employes'] ?? [];
$filters = $data['filters'] ?? [];
?>

<div class="container-fluid">
    <!-- En-tête avec boutons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Gestion des Employés</h4>
        <div>
            <a href="index.php?controller=employe&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvel employé
            </a>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="controller" value="employe">
                <input type="hidden" name="action" value="index">
                
                <div class="col-md-3">
                    <label class="form-label">Fonction</label>
                    <select name="fonction" class="form-select">
                        <option value="">Toutes les fonctions</option>
                        <option value="chef" <?php echo ($filters['fonction'] ?? '') == 'chef' ? 'selected' : ''; ?>>Chef</option>
                        <option value="ouvrier" <?php echo ($filters['fonction'] ?? '') == 'ouvrier' ? 'selected' : ''; ?>>Ouvrier</option>
                        <option value="ingenieur" <?php echo ($filters['fonction'] ?? '') == 'ingenieur' ? 'selected' : ''; ?>>Ingénieur</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?php echo ($filters['statut'] ?? '') == 'actif' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactif" <?php echo ($filters['statut'] ?? '') == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                        <option value="congé" <?php echo ($filters['statut'] ?? '') == 'congé' ? 'selected' : ''; ?>>Congé</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="index.php?controller=employe&action=index" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tableau des employés -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Liste des employés</h5>
        </div>
        <div class="card-body">
            <?php if (empty($employes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun employé trouvé.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="tableEmployes">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Fonction</th>
                            <th>Salaire journalier</th>
                            <th>Date embauche</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employes as $employe): ?>
                        <tr>
                            <td>
                                <span class="badge bg-dark"><?php echo htmlspecialchars($employe['matricule']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($employe['nom']); ?></td>
                            <td><?php echo htmlspecialchars($employe['prenom']); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $employe['fonction'] == 'chef' ? 'bg-primary' : 
                                           ($employe['fonction'] == 'ingenieur' ? 'bg-info' : 'bg-secondary'); ?>">
                                    <?php echo ucfirst($employe['fonction']); ?>
                                </span>
                            </td>
                            <td><?php echo number_format($employe['salaire_journalier'], 2, ',', ' '); ?> FBU</td>
                            <td><?php echo date('d/m/Y', strtotime($employe['date_embauche'])); ?></td>
                            <td><?php echo htmlspecialchars($employe['telephone'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $employe['statut'] == 'actif' ? 'bg-success' : 
                                           ($employe['statut'] == 'congé' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($employe['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="index.php?controller=employe&action=view&id=<?php echo $employe['id']; ?>" 
                                       class="btn btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'chef'): ?>
                                    <a href="index.php?controller=employe&action=edit&id=<?php echo $employe['id']; ?>" 
                                       class="btn btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="index.php?controller=employe&action=delete&id=<?php echo $employe['id']; ?>" 
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
            const rows = document.querySelectorAll('#tableEmployes tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>