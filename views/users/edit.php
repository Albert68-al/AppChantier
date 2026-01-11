<?php
// views/users/edit.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireRole('admin');

$page_title = "Modifier utilisateur";

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
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier l'utilisateur : <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h5>
                    <p class="text-muted mb-0">@<?php echo htmlspecialchars($user['username']); ?></p>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rôle *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                        <option value="chef" <?php echo $user['role'] == 'chef' ? 'selected' : ''; ?>>Chef de chantier</option>
                                        <option value="comptable" <?php echo $user['role'] == 'comptable' ? 'selected' : ''; ?>>Comptable</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Laisser vide pour ne pas changer">
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Laisser vide si inchangé">
                                </div>

                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle"></i>
                                        Le mot de passe ne sera modifié que si tu saisis un nouveau mot de passe.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=user&action=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
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
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    function checkPasswords() {
        if (!password.value && !confirmPassword.value) {
            confirmPassword.setCustomValidity('');
            return;
        }
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    password.addEventListener('input', checkPasswords);
    confirmPassword.addEventListener('input', checkPasswords);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
