<?php
// views/materiaux/index.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

// Vérifier le rôle
if (!$auth->hasRole('admin') && !$auth->hasRole('comptable')) {
    $_SESSION['error'] = "Accès refusé !";
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$page_title = "Matériaux";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$materiaux = $data['materiaux'] ?? [];
$alertes = $data['alertes'] ?? [];
$filters = $data['filters'] ?? [];
?>

<div class="container-fluid">
    <!-- En-tête avec boutons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Gestion des Matériaux</h4>
        <div>
            <a href="index.php?controller=materiau&action=create" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> Nouveau matériau
            </a>
            <a href="index.php?controller=materiau&action=consommer" class="btn btn-warning">
                <i class="fas fa-tools"></i> Consommer matériel
            </a>
        </div>
    </div>
    
    <!-- Alertes stock -->
    <?php if (!empty($alertes)): ?>
    <div class="alert alert-danger mb-4">
        <h5><i class="fas fa-exclamation-triangle"></i> Alertes Stock</h5>
        <p>Certains matériaux sont en dessous du seuil d'alerte :</p>
        <div class="row">
            <?php foreach ($alertes as $materiau): ?>
            <div class="col-md-3 mb-2">
                <div class="alert alert-warning py-2">
                    <strong><?php echo htmlspecialchars($materiau['nom']); ?></strong><br>
                    <small>Stock : <?php echo $materiau['quantite_disponible']; ?> <?php echo $materiau['unite_mesure']; ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="controller" value="materiau">
                <input type="hidden" name="action" value="index">
                
                <div class="col-md-3">
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="categorie" class="form-control" 
                           placeholder="Filtrer par catégorie" 
                           value="<?php echo htmlspecialchars($filters['categorie'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fournisseur</label>
                    <input type="text" name="fournisseur" class="form-control" 
                           placeholder="Filtrer par fournisseur" 
                           value="<?php echo htmlspecialchars($filters['fournisseur'] ?? ''); ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="index.php?controller=materiau&action=index" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tableau des matériaux -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Liste des matériaux</h5>
        </div>
        <div class="card-body">
            <?php if (empty($materiaux)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun matériau trouvé.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="tableMateriaux">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Stock</th>
                            <th>Unité</th>
                            <th>Prix unitaire</th>
                            <th>Valeur stock</th>
                            <th>Fournisseur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materiaux as $materiau): 
                            $valeur_stock = $materiau['quantite_disponible'] * $materiau['prix_unitaire'];
                        ?>
                        <tr class="<?php echo $materiau['quantite_disponible'] <= $materiau['seuil_alerte'] ? 'table-warning' : ''; ?>">
                            <td>
                                <span class="badge bg-dark"><?php echo htmlspecialchars($materiau['reference']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($materiau['nom']); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($materiau['categorie']); ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php echo $materiau['quantite_disponible']; ?>
                                    <?php if ($materiau['quantite_disponible'] <= $materiau['seuil_alerte']): ?>
                                    <i class="fas fa-exclamation-triangle text-danger ms-2"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($materiau['unite_mesure']); ?></td>
                            <td><?php echo number_format($materiau['prix_unitaire'], 2, ',', ' '); ?> FBU</td>
                            <td class="fw-bold"><?php echo number_format($valeur_stock, 2, ',', ' '); ?> FBU</td>
                            <td><?php echo htmlspecialchars($materiau['fournisseur'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="index.php?controller=materiau&action=edit&id=<?php echo $materiau['id']; ?>" 
                                       class="btn btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="index.php?controller=materiau&action=delete&id=<?php echo $materiau['id']; ?>" 
                                       class="btn btn-outline-danger btn-delete" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary">
                            <td colspan="6" class="text-end fw-bold">Valeur totale du stock :</td>
                            <td class="fw-bold">
                                <?php 
                                $valeur_totale = 0;
                                foreach ($materiaux as $m) {
                                    $valeur_totale += $m['quantite_disponible'] * $m['prix_unitaire'];
                                }
                                echo number_format($valeur_totale, 2, ',', ' ') . ' FBU';
                                ?>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
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
            const rows = document.querySelectorAll('#tableMateriaux tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>