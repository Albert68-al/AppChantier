<?php
// views/users/view.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireRole('admin');

$page_title = "Détails utilisateur";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$user = $data['user'] ?? null;

if (!$user) {
    echo '<div class="alert alert-danger">Utilisateur non trouvé</div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h5>
                        <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                    </div>
                    <div class="btn-group" role="group">
                        <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="index.php?controller=user&action=delete&id=<?php echo $user['id']; ?>" class="btn btn-outline-danger btn-delete">
                            <i class="fas fa-trash"></i> Désactiver
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Email</span>
                                    <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Téléphone</span>
                                    <strong><?php echo htmlspecialchars($user['telephone'] ?? 'N/A'); ?></strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Rôle</span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($user['role']); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Statut</span>
                                    <span class="badge <?php echo $user['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $user['status'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Créé le</span>
                                    <strong><?php echo $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?></strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Dernière connexion</span>
                                    <strong><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'; ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-info-circle"></i> Notes</h6>
                                <p class="mb-0">Cet écran est une vue de lecture. Pour modifier les informations, utilise le bouton “Modifier”.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php?controller=user&action=index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
