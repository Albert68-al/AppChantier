<?php
// views/materiaux/create.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

// Vérifier le rôle
if (!$auth->hasRole('admin') && !$auth->hasRole('comptable')) {
    $_SESSION['error'] = "Accès refusé !";
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$page_title = "Nouveau matériau";

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Créer un nouveau matériau</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=materiau&action=create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference" class="form-label">Référence *</label>
                                    <input type="text" class="form-control" id="reference" name="reference" required>
                                </div>

                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>

                                <div class="mb-3">
                                    <label for="categorie" class="form-label">Catégorie *</label>
                                    <input type="text" class="form-control" id="categorie" name="categorie" required>
                                </div>

                                <div class="mb-3">
                                    <label for="unite_mesure" class="form-label">Unité de mesure *</label>
                                    <input type="text" class="form-control" id="unite_mesure" name="unite_mesure" placeholder="ex: sac, kg, m" required>
                                </div>

                                <div class="mb-3">
                                    <label for="fournisseur" class="form-label">Fournisseur</label>
                                    <input type="text" class="form-control" id="fournisseur" name="fournisseur">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantite_disponible" class="form-label">Quantité disponible *</label>
                                    <input type="number" class="form-control" id="quantite_disponible" name="quantite_disponible" step="0.01" min="0" value="0" required>
                                </div>

                                <div class="mb-3">
                                    <label for="seuil_alerte" class="form-label">Seuil d'alerte *</label>
                                    <input type="number" class="form-control" id="seuil_alerte" name="seuil_alerte" step="0.01" min="0" value="10" required>
                                </div>

                                <div class="mb-3">
                                    <label for="prix_unitaire" class="form-label">Prix unitaire (FBU) *</label>
                                    <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" step="0.01" min="0" required>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=materiau&action=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer le matériau
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
