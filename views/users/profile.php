<?php
// views/users/profile.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Mon Profil";

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
        <div class="col-md-4">
            <!-- Carte profil -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-circle bg-primary mx-auto mb-3">
                            <i class="fas fa-user text-white" style="font-size: 3rem; line-height: 100px;"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h4>
                        <p class="text-muted">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($user['role']); ?></span>
                        </p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user me-2"></i>Nom d'utilisateur</span>
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-envelope me-2"></i>Email</span>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-phone me-2"></i>Téléphone</span>
                            <span><?php echo htmlspecialchars($user['telephone'] ?? 'Non renseigné'); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-alt me-2"></i>Membre depuis</span>
                            <span><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-sign-in-alt me-2"></i>Dernière connexion</span>
                            <span><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Formulaire de modification -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier mes informations</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=user&action=profile">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" 
                                           value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" 
                                           value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Changer le mot de passe</h6>
                        <p class="text-muted mb-3">Laissez vide si vous ne souhaitez pas changer le mot de passe.</p>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Informations de session -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de session</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-secondary">
                                <h6><i class="fas fa-desktop"></i> Session active</h6>
                                <p class="mb-0">
                                    <strong>IP :</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?><br>
                                    <strong>Navigateur :</strong> <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?><br>
                                    <strong>Dernière activité :</strong> <?php echo date('d/m/Y H:i:s'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-shield-alt"></i> Sécurité</h6>
                                <p class="mb-0">
                                    Pour votre sécurité, déconnectez-vous toujours après avoir utilisé l'application.<br>
                                    Changez régulièrement votre mot de passe.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #3498db;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const currentPassword = document.getElementById('current_password').value;
        
        // Vérifier si un nouveau mot de passe est fourni
        if (newPassword || confirmPassword) {
            // Vérifier que le mot de passe actuel est fourni
            if (!currentPassword) {
                e.preventDefault();
                alert('Veuillez saisir votre mot de passe actuel pour modifier le mot de passe.');
                return false;
            }
            
            // Vérifier la correspondance des nouveaux mots de passe
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les nouveaux mots de passe ne correspondent pas.');
                return false;
            }
            
            // Vérifier la force du mot de passe
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                return false;
            }
            
            const hasUpperCase = /[A-Z]/.test(newPassword);
            const hasLowerCase = /[a-z]/.test(newPassword);
            const hasNumbers = /\d/.test(newPassword);
            
            if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.');
                return false;
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>