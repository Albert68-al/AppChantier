<?php
// views/finances/ajouter_paiement.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

// Vérifier le rôle
if (!$auth->hasRole('admin') && !$auth->hasRole('comptable')) {
    $_SESSION['error'] = "Accès refusé !";
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$page_title = "Enregistrer paiement";

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
                    <h5 class="card-title mb-0">Enregistrer un paiement</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=finance&action=ajouter_paiement">
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
                                    <label for="montant" class="form-label">Montant (FBU) *</label>
                                    <input type="number" class="form-control" id="montant" name="montant" 
                                           step="0.01" min="0" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date_paiement" class="form-label">Date du paiement *</label>
                                    <input type="date" class="form-control" id="date_paiement" name="date_paiement" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mode_paiement" class="form-label">Mode de paiement *</label>
                                    <select class="form-select" id="mode_paiement" name="mode_paiement" required>
                                        <option value="">Sélectionner un mode</option>
                                        <option value="virement">Virement</option>
                                        <option value="cheque">Chèque</option>
                                        <option value="espece">Espèces</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reference" class="form-label">Référence (numéro de chèque, virement, etc.)</label>
                                    <input type="text" class="form-control" id="reference" name="reference">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=finance&action=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer le paiement
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
    document.getElementById('date_paiement').valueAsDate = new Date();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>