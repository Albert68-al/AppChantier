<?php
// views/finances/ajouter_depense.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

// Vérifier le rôle
if (!$auth->hasRole('admin') && !$auth->hasRole('comptable')) {
    $_SESSION['error'] = "Accès refusé !";
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$page_title = "Ajouter dépense";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$chantiers = $data['chantiers'] ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ajouter une dépense</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=finance&action=ajouter_depense">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="chantier_id" class="form-label">Chantier *</label>
                                    <select class="form-select" id="chantier_id" name="chantier_id" required>
                                        <option value="">Sélectionner un chantier</option>
                                        <?php foreach ($chantiers as $chantier): ?>
                                        <option value="<?php echo $chantier['id']; ?>">
                                            <?php echo htmlspecialchars($chantier['nom'] . ' (' . $chantier['client'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="type_depense" class="form-label">Type de dépense *</label>
                                    <select class="form-select" id="type_depense" name="type_depense" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="materiel">Matériel</option>
                                        <option value="salaire">Salaire</option>
                                        <option value="transport">Transport</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="montant" class="form-label">Montant (FBU) *</label>
                                    <input type="number" class="form-control" id="montant" name="montant" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_depense" class="form-label">Date de la dépense *</label>
                                    <input type="date" class="form-control" id="date_depense" name="date_depense" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="justificatif" class="form-label">Justificatif (numéro de facture, etc.)</label>
                                    <input type="text" class="form-control" id="justificatif" name="justificatif">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=finance&action=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer la dépense
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
    // Définir la date à aujourd'hui par défaut
    document.getElementById('date_depense').valueAsDate = new Date();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>