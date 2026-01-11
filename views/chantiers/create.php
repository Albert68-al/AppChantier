<?php
// views/chantiers/create.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Nouveau chantier";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$chefs = $data['chefs'] ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Créer un nouveau chantier</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=chantier&action=create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom du chantier *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="localisation" class="form-label">Localisation *</label>
                                    <input type="text" class="form-control" id="localisation" name="localisation" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="client" class="form-label">Client *</label>
                                    <input type="text" class="form-control" id="client" name="client" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="budget_total" class="form-label">Budget total (FBU) *</label>
                                    <input type="number" class="form-control" id="budget_total" name="budget_total" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_debut" class="form-label">Date de début *</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date_fin" class="form-label">Date de fin (prévisionnelle)</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="statut" class="form-label">Statut *</label>
                                    <select class="form-select" id="statut" name="statut" required>
                                        <option value="en_cours">En cours</option>
                                        <option value="suspendu">Suspendu</option>
                                        <option value="termine">Terminé</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chef_chantier_id" class="form-label">Chef de chantier</label>
                                    <select class="form-select" id="chef_chantier_id" name="chef_chantier_id">
                                        <option value="">Non assigné</option>
                                        <?php foreach ($chefs as $chef): ?>
                                        <option value="<?php echo $chef['id']; ?>">
                                            <?php echo htmlspecialchars($chef['prenom'] . ' ' . $chef['nom']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=chantier&action=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer le chantier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Définir la date de début à aujourd'hui par défaut
    document.getElementById('date_debut').valueAsDate = new Date();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>