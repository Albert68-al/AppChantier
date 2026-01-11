<?php
// views/materiaux/edit.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

// Vérifier le rôle
if (!$auth->hasRole('admin') && !$auth->hasRole('comptable')) {
    $_SESSION['error'] = "Accès refusé !";
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$page_title = "Modifier matériau";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$materiau = $data['materiau'] ?? null;

if (!$materiau) {
    echo '<div class="alert alert-danger">Matériau non trouvé</div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier le matériau : <?php echo htmlspecialchars($materiau['nom']); ?></h5>
                    <p class="text-muted mb-0">Référence : <?php echo htmlspecialchars($materiau['reference']); ?></p>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=materiau&action=edit&id=<?php echo $materiau['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference" class="form-label">Référence *</label>
                                    <input type="text" class="form-control" id="reference" name="reference" value="<?php echo htmlspecialchars($materiau['reference']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($materiau['nom']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="categorie" class="form-label">Catégorie *</label>
                                    <input type="text" class="form-control" id="categorie" name="categorie" value="<?php echo htmlspecialchars($materiau['categorie']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="unite_mesure" class="form-label">Unité de mesure *</label>
                                    <input type="text" class="form-control" id="unite_mesure" name="unite_mesure" value="<?php echo htmlspecialchars($materiau['unite_mesure']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="fournisseur" class="form-label">Fournisseur</label>
                                    <input type="text" class="form-control" id="fournisseur" name="fournisseur" value="<?php echo htmlspecialchars($materiau['fournisseur'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantite_disponible" class="form-label">Quantité disponible *</label>
                                    <input type="number" class="form-control" id="quantite_disponible" name="quantite_disponible" step="0.01" min="0" value="<?php echo htmlspecialchars($materiau['quantite_disponible']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="seuil_alerte" class="form-label">Seuil d'alerte *</label>
                                    <input type="number" class="form-control" id="seuil_alerte" name="seuil_alerte" step="0.01" min="0" value="<?php echo htmlspecialchars($materiau['seuil_alerte']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="prix_unitaire" class="form-label">Prix unitaire (FBU) *</label>
                                    <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" step="0.01" min="0" value="<?php echo htmlspecialchars($materiau['prix_unitaire']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($materiau['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=materiau&action=index" class="btn btn-secondary">
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>
