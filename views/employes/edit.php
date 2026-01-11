<?php
// views/employes/edit.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Modifier employé";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$employe = $data['employe'] ?? null;

if (!$employe) {
    echo '<div class="alert alert-danger">Employé non trouvé</div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier l'employé : <?php echo htmlspecialchars($employe['prenom'] . ' ' . $employe['nom']); ?></h5>
                    <p class="text-muted mb-0">Matricule : <?php echo htmlspecialchars($employe['matricule']); ?></p>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=employe&action=edit&id=<?php echo $employe['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo htmlspecialchars($employe['nom']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" 
                                           value="<?php echo htmlspecialchars($employe['prenom']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($employe['email'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" 
                                           value="<?php echo htmlspecialchars($employe['telephone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fonction" class="form-label">Fonction *</label>
                                    <select class="form-select" id="fonction" name="fonction" required>
                                        <option value="chef" <?php echo $employe['fonction'] == 'chef' ? 'selected' : ''; ?>>Chef</option>
                                        <option value="ouvrier" <?php echo $employe['fonction'] == 'ouvrier' ? 'selected' : ''; ?>>Ouvrier</option>
                                        <option value="ingenieur" <?php echo $employe['fonction'] == 'ingenieur' ? 'selected' : ''; ?>>Ingénieur</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="salaire_journalier" class="form-label">Salaire journalier (FBU) *</label>
                                    <input type="number" class="form-control" id="salaire_journalier" name="salaire_journalier" 
                                           step="0.01" min="0" value="<?php echo $employe['salaire_journalier']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="statut" class="form-label">Statut *</label>
                                    <select class="form-select" id="statut" name="statut" required>
                                        <option value="actif" <?php echo $employe['statut'] == 'actif' ? 'selected' : ''; ?>>Actif</option>
                                        <option value="inactif" <?php echo $employe['statut'] == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                                        <option value="congé" <?php echo $employe['statut'] == 'congé' ? 'selected' : ''; ?>>Congé</option>
                                    </select>
                                </div>
                                
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle"></i> 
                                        Date d'embauche : <?php echo date('d/m/Y', strtotime($employe['date_embauche'])); ?><br>
                                        Matricule : <?php echo htmlspecialchars($employe['matricule']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=employe&action=view&id=<?php echo $employe['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                            <div>
                                <a href="index.php?controller=employe&action=view&id=<?php echo $employe['id']; ?>" class="btn btn-outline-secondary me-2">
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
    // Validation du salaire
    const salaireInput = document.getElementById('salaire_journalier');
    salaireInput.addEventListener('blur', function() {
        if (parseFloat(this.value) < 0) {
            this.value = 0;
        }
    });
    
    // Formater le téléphone
    const telInput = document.getElementById('telephone');
    telInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 0) {
            value = value.match(/.{1,2}/g).join(' ');
        }
        this.value = value;
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>