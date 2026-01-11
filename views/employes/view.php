<?php
// views/employes/view.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Détail employé";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$employe = $data['employe'] ?? null;
$affectations = $data['affectations'] ?? [];
$chantiers = $data['chantiers'] ?? [];

if (!$employe) {
    echo '<div class="alert alert-danger">Employé non trouvé</div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <!-- En-tête de l'employé -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($employe['prenom'] . ' ' . $employe['nom']); ?></h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-id-card"></i> Matricule : <?php echo htmlspecialchars($employe['matricule']); ?>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="fas fa-briefcase"></i> Fonction : 
                                <span class="badge 
                                    <?php echo $employe['fonction'] == 'chef' ? 'bg-primary' : 
                                           ($employe['fonction'] == 'ingenieur' ? 'bg-info' : 'bg-secondary'); ?>">
                                    <?php echo ucfirst($employe['fonction']); ?>
                                </span>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar-alt"></i> Date d'embauche : 
                                <?php echo date('d/m/Y', strtotime($employe['date_embauche'])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge 
                                <?php echo $employe['statut'] == 'actif' ? 'bg-success' : 
                                       ($employe['statut'] == 'congé' ? 'bg-warning' : 'bg-danger'); ?> fs-6">
                                <?php echo ucfirst($employe['statut']); ?>
                            </span>
                            <div class="mt-2">
                                <a href="index.php?controller=employe&action=edit&id=<?php echo $employe['id']; ?>" 
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Informations personnelles -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td width="40%"><strong>Email</strong></td>
                            <td>
                                <?php echo $employe['email'] ? 
                                    '<a href="mailto:' . htmlspecialchars($employe['email']) . '">' . 
                                    htmlspecialchars($employe['email']) . '</a>' : 
                                    '<span class="text-muted">Non renseigné</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Téléphone</strong></td>
                            <td><?php echo htmlspecialchars($employe['telephone'] ?? 'Non renseigné'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Salaire journalier</strong></td>
                            <td><?php echo number_format($employe['salaire_journalier'], 2, ',', ' '); ?> FBU</td>
                        </tr>
                        <tr>
                            <td><strong>Ancienneté</strong></td>
                            <td>
                                <?php 
                                $dateEmbauche = new DateTime($employe['date_embauche']);
                                $aujourdhui = new DateTime();
                                $difference = $dateEmbauche->diff($aujourdhui);
                                echo $difference->y . ' ans, ' . $difference->m . ' mois';
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Affecter à un chantier -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Affecter à un chantier</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=employe&action=view&id=<?php echo $employe['id']; ?>">
                        <input type="hidden" name="affecter" value="1">
                        
                        <div class="mb-3">
                            <label for="chantier_id" class="form-label">Chantier *</label>
                            <select class="form-select" id="chantier_id" name="chantier_id" required>
                                <option value="">Sélectionner un chantier</option>
                                <?php foreach ($chantiers as $chantier): ?>
                                <option value="<?php echo $chantier['id']; ?>">
                                    <?php echo htmlspecialchars($chantier['nom'] . ' - ' . $chantier['client']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_debut" class="form-label">Date début *</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_fin" class="form-label">Date fin (prévisionnelle)</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus"></i> Affecter
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Historique des affectations -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Historique des affectations</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($affectations)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucune affectation pour cet employé.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Chantier</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($affectations as $affectation): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?controller=chantier&action=view&id=<?php echo $affectation['chantier_id']; ?>">
                                            <?php echo htmlspecialchars($affectation['chantier_nom']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($affectation['date_debut'])); ?></td>
                                    <td>
                                        <?php if ($affectation['date_fin']): ?>
                                        <?php echo date('d/m/Y', strtotime($affectation['date_fin'])); ?>
                                        <?php else: ?>
                                        <span class="badge bg-success">En cours</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$affectation['date_fin']): ?>
                                        <form method="POST" action="index.php?controller=employe&action=terminer_affectation&id=<?php echo $affectation['id']; ?>" 
                                              style="display: inline;">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Terminer cette affectation ?')">
                                                <i class="fas fa-check"></i> Terminer
                                            </button>
                                        </form>
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
document.addEventListener('DOMContentLoaded', function() {
    // Définir la date de début à aujourd'hui par défaut
    document.getElementById('date_debut').valueAsDate = new Date();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>