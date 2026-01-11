<?php
// views/users/index.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireRole('admin');

$page_title = "Utilisateurs";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$users = $data['users'] ?? [];
?>

<div class="container-fluid">
    <!-- En-tête avec boutons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Gestion des Utilisateurs</h4>
        <div>
            <a href="index.php?controller=user&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvel utilisateur
            </a>
        </div>
    </div>
    
    <!-- Tableau des utilisateurs -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Liste des utilisateurs</h5>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun utilisateur trouvé.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="tableUsers">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Téléphone</th>
                            <th>Dernière connexion</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                            <td>
                                <span class="badge bg-dark"><?php echo htmlspecialchars($user['username']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $user['role'] == 'admin' ? 'bg-danger' : 
                                           ($user['role'] == 'chef' ? 'bg-primary' : 'bg-success'); ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['telephone'] ?? 'N/A'); ?></td>
                            <td>
                                <?php echo $user['last_login'] ? 
                                    date('d/m/Y H:i', strtotime($user['last_login'])) : 
                                    '<span class="text-muted">Jamais</span>'; ?>
                            </td>
                            <td>
                                <span class="badge 
                                    <?php echo $user['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $user['status'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="index.php?controller=user&action=view&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="index.php?controller=user&action=delete&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-outline-danger btn-delete" title="Désactiver">
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
            const rows = document.querySelectorAll('#tableUsers tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>