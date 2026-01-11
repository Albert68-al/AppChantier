<?php
// views/chantiers/edit.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Modifier chantier";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$chantier = $data['chantier'] ?? null;
$chefs = $data['chefs'] ?? [];

if (!$chantier) {
    echo '<div class="alert alert-danger">Chantier non trouvé</div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier le chantier : <?php echo htmlspecialchars($chantier['nom']); ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=chantier&action=edit&id=<?php echo $chantier['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom du chantier *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo htmlspecialchars($chantier['nom']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="localisation" class="form-label">Localisation *</label>
                                    <input type="text" class="form-control" id="localisation" name="localisation" 
                                           value="<?php echo htmlspecialchars($chantier['localisation']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="client" class="form-label">Client *</label>
                                    <input type="text" class="form-control" id="client" name="client" 
                                           value="<?php echo htmlspecialchars($chantier['client']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="budget_total" class="form-label">Budget total (FBU) *</label>
                                    <input type="number" class="form-control" id="budget_total" name="budget_total" 
                                           step="0.01" min="0" value="<?php echo $chantier['budget_total']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_debut" class="form-label">Date de début *</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                           value="<?php echo $chantier['date_debut']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date_fin" class="form-label">Date de fin (prévisionnelle)</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                           value="<?php echo $chantier['date_fin']; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="statut" class="form-label">Statut *</label>
                                    <select class="form-select" id="statut" name="statut" required>
                                        <option value="en_cours" <?php echo $chantier['statut'] == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                                        <option value="suspendu" <?php echo $chantier['statut'] == 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                                        <option value="termine" <?php echo $chantier['statut'] == 'termine' ? 'selected' : ''; ?>>Terminé</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chef_chantier_id" class="form-label">Chef de chantier</label>
                                    <select class="form-select" id="chef_chantier_id" name="chef_chantier_id">
                                        <option value="">Non assigné</option>
                                        <?php foreach ($chefs as $chef): ?>
                                        <option value="<?php echo $chef['id']; ?>" 
                                            <?php echo $chantier['chef_chantier_id'] == $chef['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($chef['prenom'] . ' ' . $chef['nom']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($chantier['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=chantier&action=view&id=<?php echo $chantier['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                            <div>
                                <a href="index.php?controller=chantier&action=view&id=<?php echo $chantier['id']; ?>" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times"></i> Abandonner
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const budget = parseFloat(document.getElementById('budget_total').value);
        const dateDebut = new Date(document.getElementById('date_debut').value);
        const dateFin = document.getElementById('date_fin').value;
        
        if (budget <= 0) {
            e.preventDefault();
            alert('Le budget doit être supérieur à 0 !');
            return false;
        }
        
        if (dateFin) {
            const dateFinObj = new Date(dateFin);
            if (dateFinObj < dateDebut) {
                e.preventDefault();
                alert('La date de fin ne peut pas être antérieure à la date de début !');
                return false;
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>